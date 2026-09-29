

var positions = [
  ['top', 'left'],    // Case 0
  ['top', 'right'],   // Case 1
  ['bottom', 'right'],// Case 2
  ['bottom', 'left']  // Case 3
];

var g = positions[toast_position]?.[0] || 'top'; 
var p = positions[toast_position]?.[1] || 'right';

function showErrorToast(msg){
  Toastify({
      text: msg,
      gravity: g,
      position: p,
      close: true,
      style: {
        background: "linear-gradient(to right, rgb(255, 95, 109), rgb(255, 195, 113))",
      }
    }).showToast();
}
function showSuccessToast(msg){
  Toastify({
      text: msg,
      gravity: g,
      position: p,
      close: true,
      style: {
        background: "linear-gradient(to right, rgb(0, 176, 155), rgb(150, 201, 61))",
      }
    }).showToast();
}




