

var toastPositions = {
  0: 'top-start',
  1: 'top-end',
  2: 'bottom-end',
  3: 'bottom-start',
  4: 'center'
};
  
var cls = toastPositions[toast_position] || 'center';

function showErrorToast(msg){
  swal({
    title: 'Error',
    type: 'error',
    text: msg,
    timer: 2000,   
    position:cls,
    showConfirmButton: false 
  });
}
function showSuccessToast(msg){
  swal({
    title: 'Success',
    text: msg,
    type: 'success',
    timer: 2000,  
    position:cls,
    showConfirmButton: false 
  });
 
}



