<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Solar_pro_calculator
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('solar_pro/solar_pro');
    }

    public function calculate(array $input, $googleInsights = null)
    {
        $annualConsumption = max(0, (float) ($input['annual_consumption_kwh'] ?? 0));
        $panelWatts = max(1, (float) ($input['panel_watts'] ?? solar_pro_setting('solar_pro_default_panel_watts', 440)));
        $targetOffset = max(1, (float) solar_pro_setting('solar_pro_target_offset_pct', 100)) / 100;
        $lossPct = min(50, max(0, (float) solar_pro_setting('solar_pro_system_loss_pct', 14)));
        $shadingLossPct = min(50, max(0, (float) solar_pro_setting('solar_pro_shading_loss_pct', 0)));
        $productionMultiplier = min(200, max(10, (float) solar_pro_setting('solar_pro_production_multiplier_pct', 100))) / 100;
        $lossFactor = 1 - (min(80, $lossPct + $shadingLossPct) / 100);

        $source = 'fallback';
        $imageryQuality = null;
        $googleBuildingName = null;
        $panelCount = 0;
        $annualProduction = 0.0;

        if (is_array($googleInsights) && !empty($googleInsights['solarPotential'])) {
            $potential = $googleInsights['solarPotential'];
            $googlePanelWatts = max(1, (float) ($potential['panelCapacityWatts'] ?? $panelWatts));
            $configs = $potential['solarPanelConfigs'] ?? [];
            $best = null;
            $targetKwh = $annualConsumption * $targetOffset;
            foreach ($configs as $config) {
                $scaledProduction = ((float) ($config['yearlyEnergyDcKwh'] ?? 0)) * ($panelWatts / $googlePanelWatts) * $lossFactor * $productionMultiplier;
                if ($scaledProduction <= 0) {
                    continue;
                }
                if ($best === null || abs($scaledProduction - $targetKwh) < abs($best['production'] - $targetKwh)) {
                    $best = [
                        'count' => (int) ($config['panelsCount'] ?? 0),
                        'production' => $scaledProduction,
                    ];
                }
            }
            if ($best && $best['count'] > 0) {
                $panelCount = $best['count'];
                $annualProduction = $best['production'];
                $source = 'google_solar';
                $imageryQuality = $googleInsights['imageryQuality'] ?? null;
                $googleBuildingName = $googleInsights['name'] ?? null;
            }
        }

        if ($panelCount <= 0) {
            $daily = $panelWatts >= 420
                ? (float) solar_pro_setting('solar_pro_daily_kwh_440', 1.65)
                : (float) solar_pro_setting('solar_pro_daily_kwh_400', 1.50);
            // Daily fallback is defined as net delivered kWh per installed panel.
            $annualPerPanel = max(1, $daily * 365 * (1 - ($shadingLossPct / 100)) * $productionMultiplier);
            $panelCount = (int) ceil(($annualConsumption * $targetOffset) / $annualPerPanel);
            $annualProduction = $panelCount * $annualPerPanel;
        }

        $systemKw = ($panelCount * $panelWatts) / 1000;
        $pricingMode = solar_pro_setting('solar_pro_pricing_mode', 'panel');
        $equipmentPanelPrice = 0.0;
        if (!empty($input['panel_equipment_id'])) {
            $equipment = $this->CI->db->where('id',(int)$input['panel_equipment_id'])->where('type','panel')->get(db_prefix().'solar_equipment')->row_array();
            if ($equipment && (float)$equipment['sale_price'] > 0) { $equipmentPanelPrice = (float)$equipment['sale_price']; }
        }
        $panelPrice = $equipmentPanelPrice > 0 ? $equipmentPanelPrice : (float) solar_pro_setting('solar_pro_price_per_panel', 1800);
        $systemPrice = $pricingMode === 'watt' && (float) solar_pro_setting('solar_pro_price_per_watt', 0) > 0
            ? ($systemKw * 1000 * (float) solar_pro_setting('solar_pro_price_per_watt', 0))
            : ($panelCount * $panelPrice);

        $rate = $this->getRate($input);
        $monthly = $this->monthlyModel($annualConsumption, $annualProduction, $rate);
        $year1Savings = array_sum(array_column($monthly, 'savings'));
        $financial = $this->financialProjection($systemPrice, $year1Savings);

        return [
            'panel_count' => $panelCount,
            'panel_watts' => $panelWatts,
            'system_kw' => round($systemKw, 3),
            'annual_production_kwh' => round($annualProduction, 2),
            'solar_offset_pct' => $annualConsumption > 0 ? round(($annualProduction / $annualConsumption) * 100, 2) : 0,
            'system_price' => round($systemPrice, 2),
            'year1_savings' => round($year1Savings, 2),
            'payback_years' => $financial['payback_years'],
            'lifetime_savings' => $financial['lifetime_savings'],
            'analysis_source' => $source,
            'imagery_quality' => $imageryQuality,
            'google_building_name' => $googleBuildingName,
            'monthly' => $monthly,
            'financial' => $financial,
            'rate' => $rate,
            'assumptions' => [
                'target_offset_pct' => $targetOffset * 100,
                'system_loss_pct' => $lossPct,
                'shading_loss_pct' => $shadingLossPct,
                'production_multiplier_pct' => $productionMultiplier * 100,
                'utility_escalation_pct' => (float) solar_pro_setting('solar_pro_utility_escalation_pct', 3),
                'solar_degradation_pct' => (float) solar_pro_setting('solar_pro_solar_degradation_pct', 0.5),
                'financial_years' => (int) solar_pro_setting('solar_pro_financial_years', 25),
                'pricing_mode' => $pricingMode,
            ],
        ];
    }

    private function getRate(array $input)
    {
        $rate = [
            'customer_charge' => (float) solar_pro_setting('solar_pro_default_customer_charge', 15),
            'energy_rate' => (float) solar_pro_setting('solar_pro_default_energy_rate', .16),
            'fuel_rate' => 0,
            'storm_charge' => 0,
            'other_monthly_charge' => 0,
            'gross_receipts_pct' => 0,
            'utility_tax_pct' => 0,
            'sales_tax_pct' => 0,
            'minimum_bill' => 0,
            'export_credit_rate' => 0,
            'net_metering' => 1,
        ];
        $rateId = (int) ($input['utility_rate_id'] ?? 0);
        if ($rateId > 0) {
            $row = $this->CI->db->where('id', $rateId)->where('active', 1)->get(db_prefix() . 'solar_utility_rates')->row_array();
            if ($row) {
                foreach ($rate as $key => $value) {
                    if (array_key_exists($key, $row)) {
                        $rate[$key] = is_numeric($row[$key]) ? (float) $row[$key] : $row[$key];
                    }
                }
            }
        }
        return $rate;
    }

    private function monthlyModel($annualConsumption, $annualProduction, array $rate)
    {
        // Florida-oriented normalized production shape; monthly weights sum to 1.
        $productionWeights = [0.066, 0.071, 0.086, 0.092, 0.101, 0.099, 0.096, 0.091, 0.084, 0.080, 0.068, 0.066];
        $consumptionWeights = [0.074, 0.067, 0.071, 0.076, 0.088, 0.102, 0.108, 0.106, 0.095, 0.082, 0.066, 0.065];
        $rows = [];
        for ($i = 0; $i < 12; $i++) {
            $production = $annualProduction * $productionWeights[$i];
            $consumption = $annualConsumption * $consumptionWeights[$i];
            $without = $this->billForKwh($consumption, $rate, 0);
            $netKwh = $consumption - $production;
            $with = $this->billForKwh(max(0, $netKwh), $rate, max(0, -$netKwh));
            $rows[] = [
                'month_number' => $i + 1,
                'production_kwh' => round($production, 2),
                'consumption_kwh' => round($consumption, 2),
                'utility_cost_without_solar' => round($without, 2),
                'utility_cost_with_solar' => round($with, 2),
                'savings' => round(max(0, $without - $with), 2),
            ];
        }
        return $rows;
    }

    private function billForKwh($importKwh, array $rate, $exportKwh = 0)
    {
        $energy = $importKwh * ($rate['energy_rate'] + $rate['fuel_rate']);
        $base = $rate['customer_charge'] + $rate['storm_charge'] + $rate['other_monthly_charge'] + $energy;
        $taxPct = ($rate['gross_receipts_pct'] + $rate['utility_tax_pct'] + $rate['sales_tax_pct']) / 100;
        $bill = $base * (1 + $taxPct);
        if ($exportKwh > 0 && $rate['export_credit_rate'] > 0) {
            $bill -= $exportKwh * $rate['export_credit_rate'];
        }
        return max((float) $rate['minimum_bill'], $bill);
    }

    private function financialProjection($systemPrice, $year1Savings)
    {
        $years = max(1, (int) solar_pro_setting('solar_pro_financial_years', 25));
        $escalation = (float) solar_pro_setting('solar_pro_utility_escalation_pct', 3) / 100;
        $degradation = (float) solar_pro_setting('solar_pro_solar_degradation_pct', .5) / 100;
        $rows = [];
        $cumulative = 0;
        $payback = null;
        for ($year = 1; $year <= $years; $year++) {
            $annualSavings = $year1Savings * pow(1 + $escalation, $year - 1) * pow(1 - $degradation, $year - 1);
            $cumulative += $annualSavings;
            if ($payback === null && $cumulative >= $systemPrice && $annualSavings > 0) {
                $previous = $cumulative - $annualSavings;
                $payback = ($year - 1) + (($systemPrice - $previous) / $annualSavings);
            }
            $rows[] = ['year' => $year, 'savings' => round($annualSavings, 2), 'cumulative' => round($cumulative, 2)];
        }
        return [
            'years' => $rows,
            'payback_years' => $payback === null ? null : round($payback, 2),
            'lifetime_savings' => round($cumulative - $systemPrice, 2),
        ];
    }
}
