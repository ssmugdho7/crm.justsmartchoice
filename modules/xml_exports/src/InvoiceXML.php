<?php

namespace Techy4m\XmlExports;
defined('BASEPATH') or exit('No direct script access allowed');

use Carbon\Carbon;
use Einvoicing\AllowanceOrCharge;
use Einvoicing\Identifier;
use Einvoicing\Invoice;
use Einvoicing\InvoiceLine;
use Einvoicing\Party;
use Einvoicing\Presets;

class InvoiceXML
{
    /**
     * @var object{
     *  'number': int,
     * 'number_format': string,
     * 'clientid': int,
     * 'currency': int,
     * 'currency_name': string,
     * 'currencyid': int,
     * 'date': string,
     * 'duedate': string,
     * 'subtotal': int,
     * 'total': int,
     * 'adjustment': int,
     * 'discount_percent': int,
     * 'discount_total': int,
     * 'discount_type': string,
     * 'terms': string,
     * 'sale_agent': string,
     * 'billing_street': string,
     * 'billing_city': string,
     * 'billing_state': string,
     * 'billing_zip': string,
     * 'billing_country': string,
     * 'shipping_street': string,
     * 'shipping_city': string,
     * 'shipping_state': string,
     * 'shipping_zip': string,
     * 'shipping_country': string,
     * 'show_shipping_on_invoice': string,
     * 'include_shipping': string,
     * 'clientnote': string,
     * 'adminnote': string,
     * 'allowed_payment_modes': string,
     * 'items': object,
     * 'total_left_to_pay': float,
     * 'project_id': string|null,
     * 'client': object,
     * 'payments': array<mixed, object>,
     *  'prefix':string,
     * }
     */
    private object $invoice;

    /**
     * @var object{
     *      'id': int,
     *       'company' : string,
     *       'vat': string,
     *  'phonenumber': string,
     *  'country': string,
     *  'city': string,
     *  'zip': string,
     *  'state': string,
     *  'address': string,
     *  'website': string,
     *  'active': string,
     *  'billing_street': string,
     *  'billing_city': string,
     *  'billing_state': string,
     *  'billing_zip': string,
     *  'billing_country': string,
     *  'shipping_street': string,
     *  'shipping_city': string,
     *  'shipping_state': string,
     *  'shipping_zip': string,
     *  'shipping_country': string,
     *  'longitude': string,
     *  'latitude': string,
     *  'show_primary_contact': string,
     *  'userid': int,
     * }
     */
    private object $client;

    public function __construct(object $invoice)
    {
        $this->invoice = $invoice;
        $this->client = $invoice->client;
    }

    public function getPreset(): string
    {
        $default = Presets\Peppol::class;
        if (get_option('invoice_company_country_code') == 'RO') {
            return Presets\CiusRo::class;
        }

        return $default;
    }

    public function generate(): Invoice
    {
        $xml = new Invoice($this->getPreset());
        $xml->setNumber($this->invoice->number)
            ->setIssueDate(Carbon::parse($this->invoice->date)->toDate())
            ->setDueDate(Carbon::parse($this->invoice->duedate)->toDate());

        if (trim($this->invoice->clientnote) !== '') {
            $xml->addNote($this->invoice->clientnote);
        }

        if (!empty($this->invoice->discount_type) && $this->invoice->discount_total > 0) {
            $discount = new AllowanceOrCharge();
            $discount->setReason(_l('invoice_discount'));
            if ((int)$this->invoice->discount_percent === 0) {
                $discount->markAsFixedAmount();
                $discount->setAmount($this->invoice->discount_total);
            } else {
                $discount->markAsPercentage();
                $discount->setAmount($this->invoice->discount_percent);
            }

            $xml->addAllowance($discount);
        }

        if ($this->invoice->adjustment > 0) {
            $adjustment = new AllowanceOrCharge();
            $adjustment->setReason(_l('invoice_adjustment'));
            $adjustment->markAsFixedAmount();
            $adjustment->setAmount($this->invoice->adjustment);
            $xml->addAllowance($adjustment);
        }


        $xml->setCurrency($this->invoice->currency_name);
        $xml->setPaidAmount(collect($this->invoice->payments)->sum('amount'));

        $xml->setSeller($this->getSeller());
        $xml->setBuyer($this->getBuyer());

        $reference = get_custom_field_value($this->invoice->id, get_option('xml_export_invoice_buyer_reference_field'), 'invoice');
        $xml->setBuyerReference($reference);

        foreach (collect($this->invoice->items)->sortBy('item_order') as $item) {
            $xml->addLine($this->prepareLineItem($item));
        }


        return $xml;
    }

    protected function getBuyer(): Party
    {
        $eAddress = get_custom_field_value($this->client->userid, get_option('xml_export_customer_electronic_address_field'), 'customers');
        $eScheme = get_custom_field_value($this->client->userid, get_option('xml_export_customer_electronic_address_scheme_field'), 'customers');
        $buyer = new Party();
        $buyer
            ->setElectronicAddress(new Identifier(trim($eAddress), trim($eScheme)))
            ->setName($this->client->company)
            ->setContactPhone($this->client->phonenumber)
            ->setPostalCode($this->invoice->billing_zip)
            ->setCity($this->invoice->billing_city)
            ->setAddress([$this->invoice->billing_street ?? ''])
            ->setSubdivision($this->invoice->billing_state)
            ->setVatNumber($this->client->vat ?: null);
        $country = get_country($this->invoice->billing_country);
        if ($country) {
            $buyer->setCountry($country->iso2);
        }

        if ($this->client->show_primary_contact == '1') {
            $fullName = get_contact_full_name(get_primary_contact_user_id($this->client->userid));
            if (!empty($fullName)) {
                $buyer->setName(get_contact_full_name(get_primary_contact_user_id($this->client->userid)));
            }
        }

        return $buyer;
    }

    protected function getSeller(): Party
    {
        $seller = new Party();
        $seller
            ->setElectronicAddress(
                new Identifier(get_option('xml_export_electronic_address'), get_option('xml_export_electronic_address_scheme'))
            )
            ->setCompanyId(
                new Identifier(get_option('xml_export_company_id'), get_option('xml_export_company_id_scheme'))
            )
            ->setName(get_option('invoice_company_name'))
            ->setVatNumber(get_option('company_vat'))
            ->setAddress([get_option('invoice_company_address') ?? ''])
            ->setCity(get_option('invoice_company_city'))
            ->setSubdivision(get_option('company_state'))
            ->setPostalCode(get_option('invoice_company_postal_code'))
            ->setCountry(get_option('invoice_company_country_code'));

        return $seller;
    }

    /**
     * @param $item array{
     *      'description': string,
     *      'long_description': string,
     *     'qty': float,
     *     'rate':float,
     *     'unit': string,
     *     'id': int,
     * }
     * @return InvoiceLine
     */
    protected function prepareLineItem(array $item): InvoiceLine
    {
        $line = new InvoiceLine();
        $line->setName($item['description'])
            ->setDescription($item['long_description'])
            ->setPrice($item['rate'])
            ->setQuantity($item['qty']);

        $tax = collect(get_invoice_item_taxes($item['id']))->first();
        if ($tax !== null && $tax['taxrate'] > 0) {
            $line->setVatRate($tax['taxrate']);
        }

        return $line;
    }
}
