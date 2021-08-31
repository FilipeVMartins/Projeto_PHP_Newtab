

// function ajaxPostDelete(event, id, partialUrl){
//     event.preventDefault();

//     body = JSON.stringify({
//         "delete": id
//     });

//     fullUrl = window.location.origin + partialUrl;
//     fetch(fullUrl, {
//         method: 'POST',
//         headers: {
//             'content-type': 'application/json'
//         },
//         body: body
//     })
//     .then(response => {
//         console.log(response.text());

//         return response.json();
//     })
//     .then((responseJson) =>{
//         console.log(responseJson);
//     });
// }

//<td><button alt="Excluir Cliente" onclick="ajaxPostDelete(event, <?php echo $row['ID']?>, '<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>')" >?</button></td>