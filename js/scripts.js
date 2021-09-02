

function changeOffset(event) {
    var form = document.forms["search-form"];
    form.offset_atual.value = event.target.value;
    form.submit();
}

function changeDtPedido(e=null){
    //check value of both dt fields, if any != from '', then DtPedido = true
    var dtMin = new Date (document.querySelector('#dtMin').value);
    var dtMax = new Date (document.querySelector('#dtMax').value);

    if (dtMin == 'Invalid Date' && dtMax == 'Invalid Date'){
        document.querySelector('#DtPedido').value = '';
    } else
    if (dtMin != 'Invalid Date' || dtMax != 'Invalid Date') {
        document.querySelector('#DtPedido').value = true;
    }
}





















