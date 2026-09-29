 function showSuccessToast(msg) {
    $.toast({
        title: "Success!",
        message: msg,
        type: "success",
    });
}

function showErrorToast(msg) {
    $.toast({
        title: "Error!",
        message: msg,
        type: "error",
    });
}
