
function changeOffset(event) {
    var form = document.forms["search-form"];
    form.offset_atual.value = event.target.value;
    form.submit();
}