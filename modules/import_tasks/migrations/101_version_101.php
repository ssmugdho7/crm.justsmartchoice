<?

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
    }

    public function down()
    {
        // Safe no-op rollback for Smart Choice structural compatibility.
        return true;
    }
}
