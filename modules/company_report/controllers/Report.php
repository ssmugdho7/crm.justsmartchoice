<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Report extends AdminController {

    public $query_year      = "";
    public $query_month     = "";
    public $query_currency  = "";
    public $query_staff_id  = "";

    public function __construct()
    {
        parent::__construct();

        $this->check_the_db();

    }

    public function index()
    {

        $this->report_view(  );

    }


    public function year()
    {

        $this->report_view( 1 );

    }

    /**
     * @param $yearly value is 1 then compare different years
     */
    public function report_view( $yearly = 0 )
    {

        $query_year     = $this->input->post('year');
        $query_year_to  = $this->input->post('year_to');
        $query_currency = $this->input->post('currency');
        $query_staff_id = $this->input->post('staff_id');


        if( empty( $query_year ) )
            $query_year = date('Y');

        if( empty( $query_year_to ) )
            $query_year_to = date('Y');

        if( empty( $query_currency ) )
            $query_currency = get_base_currency()->id;


        $this->query_year       = $query_year;
        $this->query_currency   = $query_currency;
        $this->query_staff_id   = $query_staff_id;


        if( $yearly == 1 )
            $data["title"] = _l('yearly_activity_report');
        else
            $data["title"] = _l('monthly_activity_report');

        $data_filter_keys = [
            'leads' ,
            'clients' ,
            'tasks' ,
            'expenses' ,
            'proposal_q' ,
            'proposal_t' ,
            'payments' ,
            'invoice_q' ,
            'invoice_t' ,
            'projects_q' ,
            'projects_t' ,
            'estimate_q' ,
            'estimate_t' ,
        ];

        $data["filter_keys"] = $data_filter_keys;


        /**
         * Graph key and colors
         */
        $data['filter_amount_keys'] = [ 'expenses' , 'incomes' , 'proposal_t' , 'payments' , 'invoice_t' , 'projects_t' , 'estimate_t' ];
        $data['filter_color_keys']  = [
            'expenses'      => '#1f78b4' ,
            'incomes'       => '#33a02c' ,
            'proposal_t'    => '#e31a1c' ,
            'payments'      => '#ff7f00' ,
            'invoice_t'     => '#6a3d9a' ,
            'projects_t'    => '#b15928' ,
            'estimate_t'    => '#a6cee3'
        ];


        $filter_keys = [];

        $filters = $this->get_report_types();
        if ( !empty( $filters ) )
        {

            $data["filter_keys"] = [];

            foreach ( $filters as $filter )
            {

                $filter_keys[] = $filter->report_type;

                $data["filter_keys"][] = $filter->report_type;

            }

            foreach ( $data_filter_keys as $data_filter_key )
            {

                if( !in_array( $data_filter_key , $data["filter_keys"] ) )
                    $data["filter_keys"][] = $data_filter_key;

            }

        }
        else
            $filter_keys = $data_filter_keys;


        $data["record_keys"] = $filter_keys;


        $records = [];

        $index = 0;
        $records['reports'][$index]     = "#";
        $records['leads'][$index]       = _l('customer_report_leads');
        $records['clients'][$index]     = _l('customer_report_clients');
        $records['tasks'][$index]       = _l('customer_report_tasks');
        $records['expenses'][$index]    = _l('customer_report_expenses');
        $records['proposal_q'][$index]  = _l('customer_report_proposal_q');
        $records['proposal_t'][$index]  = _l('customer_report_proposal_t');
        $records['payments'][$index]    = _l('customer_report_payments');
        $records['invoice_q'][$index]   = _l('customer_report_invoice_q');
        $records['invoice_t'][$index]   = _l('customer_report_invoice_t');

        $records['projects_q'][$index]      = _l('customer_report_projects_q');
        $records['projects_t'][$index]      = _l('customer_report_projects_t');
        $records['estimate_q'][$index]      = _l('customer_report_estimate_q');
        $records['estimate_t'][$index]      = _l('customer_report_estimate_t');


        $index_from = 1;
        $index_to   = 13;
        if ( $yearly == 1 )
        {
            $index_from = $query_year;
            $index_to   = $query_year_to+1;
        }

        for ( $index = $index_from ; $index < $index_to ; $index++ )
        {

            /**
             * data is pulled by year
             */
            if( $yearly == 1 )
            {

                $this->query_month  = null;

                $this->query_year   = $index;

                $records['reports'][$index] = $index;

            }
            else
            {

                /**
                 * data is pulled by month
                 */
                $query_month = $index;

                if( $query_month < 10 )
                    $query_month = "0$query_month";


                $this->query_month      = $query_month;

                $records['reports'][$index] = $this->get_month_name( $query_month );

            }


            // Leads counts
            if ( in_array( 'leads' , $filter_keys ) )
                $records['leads'][$index] = $this->get_report_data_for_leads();


            // Client counts
            if ( in_array( 'clients' , $filter_keys ) )
                $records['clients'][$index] = $this->get_report_data_for_clients();


            // Task count
            if ( in_array( 'tasks' , $filter_keys ) )
                $records['tasks'][$index] = $this->get_report_data_for_tasks();


            // Expenses amount
            if ( in_array( 'expenses' , $filter_keys ) )
                $records['expenses'][$index] = $this->get_report_data_for_expenses();



            // Proposal count
            if ( in_array( 'proposal_q' , $filter_keys ) )
                $records['proposal_q'][$index]     = $this->get_report_data_for_proposal_quan();



            // Proposal amount
            if ( in_array( 'proposal_t' , $filter_keys ) )
                $records['proposal_t'][$index]     = $this->get_report_data_for_proposal_total();



            // Paymetn amount
            if ( in_array( 'payments' , $filter_keys ) )
                $records['payments'][$index]     = $this->get_report_data_for_payment();



            // Invoice count
            if ( in_array( 'invoice_q' , $filter_keys ) )
                $records['invoice_q'][$index]     = $this->get_report_data_for_invoice_quan();



            // Invoice amount
            if ( in_array( 'invoice_t' , $filter_keys ) )
                $records['invoice_t'][$index]     = $this->get_report_data_for_invoice_total();


            // Project count
            if ( in_array( 'projects_q' , $filter_keys ) )
                $records['projects_q'][$index]     = $this->get_report_data_for_project_quan();


            // Project amount
            if ( in_array( 'projects_t' , $filter_keys ) )
                $records['projects_t'][$index]     = $this->get_report_data_for_project_total();


            // Estimates count
            if ( in_array( 'estimate_q' , $filter_keys ) )
                $records['estimate_q'][$index]     = $this->get_report_data_for_estimate_quan();


            // Estimates amount
            if ( in_array( 'estimate_t' , $filter_keys ) )
                $records['estimate_t'][$index]     = $this->get_report_data_for_estimate_total();


        }


        $data["records"] = $records;

        $data["query_year"]     = $query_year;
        $data["query_year_to"]  = $query_year_to;
        $data["start_year"]     = $this->get_first_year();
        $data["query_currency"] = $query_currency;
        $data["currencies"]     = $this->get_currencies();

        $data["query_staff_id"] = $query_staff_id;
        $data["staff"]          = $this->staff_model->get('', ['active' => 1]);


        if( $yearly == 1 )
            $this->load->view('v_report_year', $data);
        else
            $this->load->view('v_report', $data);

    }

    private function get_report_data_for_leads()
    {


        if( !empty( $this->query_staff_id ) )
            $this->db->where('assigned',$this->query_staff_id);

        if( !empty( $this->query_month ) )
            $this->db->where( " MONTH(dateadded)  ",$this->query_month);

        return
            $this->db->where(" YEAR(dateadded)",$this->query_year)
                    ->count_all_results(db_prefix()."leads") ;

    }


    private function get_report_data_for_clients()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('userid IN ( SELECT customer_id FROM '.db_prefix().'customer_admins WHERE staff_id = '.$this->query_staff_id.' )',null,false);

        if( !empty( $this->query_month ) )
            $this->db->where( " MONTH(datecreated)  ",$this->query_month)  ;

        return
            $this->db->where(" YEAR(datecreated)",$this->query_year)

                ->where('active',1)
                ->count_all_results(db_prefix()."clients") ;

    }

    private function get_report_data_for_tasks()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('id IN ( SELECT taskid FROM '.db_prefix().'task_assigned WHERE staffid = '.$this->query_staff_id.' )',null,false);

        if( !empty( $this->query_month ) )
            $this->db->where( " MONTH(dateadded)  ",$this->query_month);

        return
            $this->db->where(" YEAR(dateadded)",$this->query_year)
                        ->count_all_results(db_prefix()."tasks") ;

    }


    private function get_report_data_for_expenses()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('addedfrom',$this->query_staff_id);

        if( !empty( $this->query_month ) )
            $this->db->where("MONTH(TBLExpenses.date)",$this->query_month)  ;

        return
            $this->db->select('SUM((TBLExpenses.amount + ((IFNULL(TBLTax.taxrate, 0) / 100) * TBLExpenses.amount) + ((IFNULL(TBLTax2.taxrate, 0) / 100) * TBLExpenses.amount))) AS total_amount')
                            ->from(db_prefix()."expenses TBLExpenses")
                            ->join(db_prefix()."taxes TBLTax","TBLExpenses.tax = TBLTax.id","left outer")
                            ->join(db_prefix()."taxes TBLTax2","TBLExpenses.tax2 = TBLTax2.id","left outer")
                            ->where("YEAR(TBLExpenses.date)",$this->query_year)
                            ->where("currency",$this->query_currency)
                            ->get()->row('total_amount');

    }


    private function get_report_data_for_proposal_quan()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('assigned',$this->query_staff_id);

        if( !empty( $this->query_month ) )
            $this->db->where("MONTH(date)",$this->query_month)  ;

        return
            $this->db->where("YEAR(date)",$this->query_year)
                ->where("currency",$this->query_currency)
                ->count_all_results(db_prefix()."proposals") ;

    }


    private function get_report_data_for_proposal_total()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('assigned',$this->query_staff_id);

        if( !empty( $this->query_month ) )
            $this->db->where("MONTH(date)",$this->query_month);

        return
            $this->db->select('SUM( total ) as total_amount')
                    ->from(db_prefix()."proposals")
                    ->where("YEAR(date)",$this->query_year)
                    ->where("currency",$this->query_currency)
                    ->get()->row('total_amount');


    }


    private function get_report_data_for_payment()
    {

        if( !empty( $this->query_staff_id ) )
            return null;

        if( !empty( $this->query_month ) )
            $this->db->where("MONTH(py.date)",$this->query_month)  ;

        return
            $this->db->select('SUM( py.amount ) as total_amount')
                    ->from(db_prefix()."invoicepaymentrecords py")
                    ->join(db_prefix()."invoices inv","inv.id = py.invoiceid")
                    ->where("YEAR(py.date)",$this->query_year)
                    ->where("inv.currency",$this->query_currency)
                    ->get()->row('total_amount');


    }


    private function get_report_data_for_invoice_quan()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('sale_agent',$this->query_staff_id);

        if( !empty( $this->query_month ) )
            $this->db->where("MONTH(date)",$this->query_month)  ;

        return
            $this->db->where("YEAR(date)",$this->query_year)
                    ->where("currency",$this->query_currency)
                    ->where("status !=",5)
                    ->count_all_results(db_prefix()."invoices") ;

    }


    private function get_report_data_for_invoice_total()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('sale_agent',$this->query_staff_id);

        if( !empty( $this->query_month ) )
            $this->db->where("MONTH(date)",$this->query_month)  ;

        return
            $this->db->select('SUM( total ) as total_amount')
                ->from(db_prefix()."invoices")
                ->where("YEAR(date)",$this->query_year)
                ->where("currency",$this->query_currency)
                ->where("status !=",5)
                ->get()->row('total_amount');

    }


    private function get_report_data_for_project_quan()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('id IN ( SELECT project_id FROM '.db_prefix().'project_members WHERE staff_id = '.$this->query_staff_id.' )',null,false);

        if( !empty( $this->query_month ) )
            $this->db->where( " MONTH(start_date)  ",$this->query_month)  ;

        return
            $this->db->where(" YEAR(start_date)",$this->query_year)
                ->where("status !=",5)
                ->count_all_results(db_prefix()."projects") ;

    }


    private function get_report_data_for_project_total()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('id IN ( SELECT project_id FROM '.db_prefix().'project_members WHERE staff_id = '.$this->query_staff_id.' )',null,false);

        if( !empty( $this->query_month ) )
            $this->db->where( " MONTH(start_date)  ",$this->query_month);

        return
            $this->db->select('SUM(project_cost) as total_amount')
                    ->from(db_prefix()."projects")
                    ->where(" YEAR(start_date)",$this->query_year)
                    ->where("status !=",5)
                    ->get()->row('total_amount');


    }


    private function get_report_data_for_estimate_quan()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('sale_agent',$this->query_staff_id);

        if( !empty( $this->query_month ) )
            $this->db->where( " MONTH(date)  ",$this->query_month) ;

        return
            $this->db->where(" YEAR(date)",$this->query_year)
                    ->where("status !=",5)
                    ->count_all_results(db_prefix()."estimates") ;


    }


    private function get_report_data_for_estimate_total()
    {

        if( !empty( $this->query_staff_id ) )
            $this->db->where('sale_agent',$this->query_staff_id);

        if( !empty( $this->query_month ) )
            $this->db->where( " MONTH(date)  ",$this->query_month)  ;

        return
            $this->db->select('SUM(total) as total_amount')
                    ->from(db_prefix()."estimates")
                    ->where(" YEAR(date)",$this->query_year)
                    ->where("status !=",5)
                    ->get()->row('total_amount');

    }



    /**
     * @note The starting year of the reports is based on the first registered personnel. No records can be processed without personnel
     *
     * @return int year
     */
    private function get_first_year()
    {

        $info = $this->db->select('MIN( YEAR( datecreated ) ) as year')->from(db_prefix().'staff')->get()->row();

        if( !empty( $info->year ) )
            return $info->year;

        return 2020;

    }

    private function get_currencies()
    {
        return $this->db->select('id, name')->from(db_prefix().'currencies')->get()->result();
    }

    private function get_month_name( $month_id = 0 )
    {

        if( !empty( $month_id ) )
            $month_id = (int)$month_id;

        $months = [
            1 => _l('January') ,
            2 => _l('February'),
            3 => _l('March'),
            4 => _l('April'),
            5 => _l('May'),
            6 => _l('June'),
            7 => _l('July'),
            8 => _l('August'),
            9 => _l('September'),
            10 => _l('October'),
            11 => _l('November'),
            12 => _l('December')
        ];


        if( !empty( $months[ $month_id ] ) )
            return $months[ $month_id ];


        return $month_id;


    }


    /**
     * Report row order saving
     */
    public function report_save_changes()
    {

        $report_types = $this->input->get('report_type');

        if ( !empty( $report_types ) )
        {

            $table = db_prefix().'company_report_module_data_orders';

            $this->db->truncate($table);

            foreach ( $report_types as $report_type)
            {

                $this->db->insert($table,[ 'report_type' => $report_type ]);

            }

        }

    }

    public function get_report_types()
    {

        return $this->db->select('*')->from( db_prefix().'company_report_module_data_orders')->order_by('id','asc')->get()->result();

    }


    private function check_the_db()
    {
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . 'company_report_module_data_orders'))
        {
            $CI->db->query("CREATE TABLE `".db_prefix()."company_report_module_data_orders` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `report_type` varchar(100) DEFAULT NULL,
                                  PRIMARY KEY (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;"
            );
        }

    }


}
