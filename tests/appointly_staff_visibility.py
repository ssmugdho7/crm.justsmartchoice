"""Execute the production table's visibility expression against the actual attendee schema."""
import pathlib,re,sqlite3,subprocess
root=pathlib.Path(__file__).resolve().parent.parent
source=(root/'modules/appointly/views/tables/index.php').read_text()
block=source.split("if (! is_admin() && ! staff_can('view', 'appointments')) {",1)[1].split('\n}',1)[0]
php="<?php function db_prefix(){return 'tbl';} function get_staff_user_id(){return 7;} $where=[];"+block+";echo $where[0];"
r=subprocess.run(['php'],input=php,text=True,capture_output=True,check=True)
where=r.stdout
con=sqlite3.connect(':memory:')
con.executescript('CREATE TABLE tblappointly_appointments(id INTEGER,created_by INTEGER,status TEXT);CREATE TABLE tblappointly_attendees(appointment_id INTEGER,staff_id INTEGER);INSERT INTO tblappointly_appointments VALUES(1,7,"pending"),(2,8,"pending"),(3,8,"pending"),(4,7,"completed"),(5,8,"completed");INSERT INTO tblappointly_attendees VALUES(2,7),(3,9),(5,7);')
def ids(extra=''):
 return [r[0] for r in con.execute('SELECT id FROM tblappointly_appointments WHERE 1=1 '+where+extra+' ORDER BY id')]
assert ids()==[1,2,4,5],ids()
assert ids(' AND status="pending"')==[1,2],ids(' AND status="pending"')
assert ids(' AND status="completed"')==[4,5]
assert ids(' AND id=3')==[], 'Other staff appointment exposed'
print('PASS owner+attendee visibility, correct schema, status/search filtering and other-staff isolation')
