# perfex-tasks-importer

Perfex CRM Tasks Importer module version : `0.1.0`

1. Your CSV data should be in the format below. The first line of your CSV file should be the column headers as in the table example. Also make sure that your file encoding is UTF-8 to avoid unnecessary encoding problems.<br>
2. If the column you are trying to import is date make sure that is formatted in format Y-m-d (2022-09-22).<br>
3. In the "Addedfrom" column, put the employee's id or email address, and when in the "Is added from contact" column the value is 1, then in the "Addedfrom" column, put contact's (customer's) id or email.<br>
4. The "Recurring type" column - this column can take one of the values ​​(day, week, month, year).<br>
The "Repeat every" column contains an integer (the number of units from the "Recurring type" column to repeat the job).<br>
The "Recurring" column with a value of 0 means repetition is off or 1 is on.<br>
The "Is recurring from" column - integer means repeating from the selected unit.<br>
The "Cycles" column - means the number of repetitions (cycles).<br>
The "Total cycles" column means the number of cycles performed - 0 means an infinite number of cycles.<br>
The "Custom recurring" column is marked 1 if the task is to be repeated at non-standard intervals.<br>
The "Last recurring date" column holds the last recurring date of the job.<br>
5. The "Rel id" column indicates the related customer or other model selected in the Rel type field.<br>
The "Rel type" column indicates the related model (possible values ​​"project", "invoice", "customer", "estimate", "contract", "ticket", "expense", "lead", "proposal").<br>
6. The "Is public" column - Whether the task should be visible to all employees or only to assignees, creator and administrators.<br>
The "Billable" column - determines whether the task can be factored.<br>
The "Billed" column - specifies whether an invoice for the task has been issued.<br>
The "Invoice id" column - indicates the invoice ID, if it was issued.<br>
The "Hourly rate" column - stores the individual hourly rate per task.<br>
The "Milestone" column - milestone identifier.<br>
The "Kanban order" column - location on the kanabana.<br>
The "Milestone order" column - location on milestones.<br>
The "Visible to client" column - determines whether the task should be visible to the client.<br>
The "Deadline notified" column - indicates whether a notification about the end of the task deadline has been sent.<br>
The "Attachments" column - Indicates comma separated URLs with attachments to the task.<br>
The "Tags" column - contains tags separated by commas.<br>
The "Checklist items" column - contains points from the checklist separated by commas.<br>
The "Cf ..." columns - all columns beginning with "Cf" contain values ​​for Custom Fields.
After the tag "Cf" you should put the id or the name of a custom field.
