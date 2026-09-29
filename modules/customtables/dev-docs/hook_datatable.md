| No.  | Features  | Hooks Name                                                   |
| ---- | --------- | ------------------------------------------------------------ |
| 1    | Leads     | hooks()->apply_filters('leads_table_sql_columns', $aColumns)<br/>hooks()->apply_filters('leads_table_additional_columns_sql', $addistionalColumn)<br/>hooks()->apply_filters('leads_table_row_data', $row, $aRow)<br/>hooks()->apply_filters('leads_table_columns', $table_data); |
| 2    | Customers | hooks()->apply_filters('customers_table_sql_join', $join);<br/>hooks()->apply_filters('customers_table_sql_columns', $aColumns);<br/>hooks()->apply_filters('customers_table_row_data', $row, $aRow);<br/>hooks()->apply_filters('customers_table_columns', $table_data); |
| 3    | Proposals | hooks()->apply_filters('proposals_table_sql_columns', $aColumns);<br/>hooks()->apply_filters('proposals_table_row_data', $row, $aRow);<br/>hooks()->apply_filters('proposals_table_columns', $table_data); |
| 4    | Estimates | hooks()->apply_filters('estimates_table_sql_columns', $aColumns);<br/>hooks()->apply_filters('estimates_table_row_data', $row, $aRow);<br/>hooks()->apply_filters('estimates_table_columns', $table_data); |
| 5    | Invoices  | hooks()->apply_filters('invoices_table_sql_columns', $aColumns);<br/>hooks()->apply_filters('invoices_table_row_data', $row, $aRow);<br/>hooks()->apply_filters('invoices_table_columns', $table_data); |
| 6    | Expenses  | hooks()->apply_filters('expenses_table_sql_columns', $aColumns);<br/>hooks()->apply_filters('expenses_table_row_data', $row, $aRow);<br/>hooks()->apply_filters('expenses_table_columns', $table_data); |
| 7    | Projects  | hooks()->apply_filters('projects_table_sql_columns', $aColumns);<br/>hooks()->apply_filters('projects_table_row_data', $row, $aRow);<br/>hooks()->apply_filters('projects_table_columns', $table_data); |
| 8    | Tasks     | hooks()->apply_filters('tasks_table_sql_columns', $aColumns);<br/>hooks()->apply_filters('tasks_table_row_data', $row, $aRow);<br/>hooks()->apply_filters('tasks_table_columns', $table_data); |
| 9    | Contracts | hooks()->apply_filters('contracts_table_sql_columns', $aColumns);<br/>hooks()->apply_filters('contracts_table_row_data', $row, $aRow);<br/>hooks()->apply_filters('contracts_table_columns', $table_data); |

### Customized Tables List

| No.  | Table Name | Main Table | Project Section | Customer Profile |
| ---- | ---------- | ---------- | --------------- | ---------------- |
| 1    | Leads      | ✔          |                 |                  |
| 2    | Customers  | ✔          |                 |                  |
| 3    | Proposals  | ✔          | ✔               |                  |
| 4    | Estimates  | ✔          | ✔               | ✔                |
| 5    | Invoices   | ✔          | ✔               | ✔                |
| 6    | Expenses   | ✔          |                 |                  |
| 7    | Projects   | ✔          |                 | ✔                |
| 8    | Tasks      | ✔          |                 |                  |
| 9    | Contracts  | ✔          |                 |                  |

