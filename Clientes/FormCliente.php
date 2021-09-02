<?php
require_once '../dbFunctions/dbConnection.php';

//print_r($_POST);
//print_r($_GET);
$tableName = 'Cliente';
$tablePK = 'ID';
$form = 'new';
if ($_GET){
    
    if (isset($_GET['edit'])){
        $form = 'edit';
        $result = executeSelectByID($_GET['edit'], $tablePK, $tableName)[0];
    }

} elseif ($_POST) {
    $form = 'edit';

    //update
    if (isset($_POST['save'])){
        //after save, return $result if successful
        $result = executeUpdateByID($_POST[$tablePK], $tablePK, $tableName);

        //se falhar
        if (isset($result['error']) == True){
            //return the last inputed data to be autofilled
            $result = array_merge($result, $_POST);
        } else {
            $result['success'] = 'Registro alterado!';
        }

    //insert
    } elseif (isset($_POST['savenew'])){

        $result = executeInsert($tablePK, $tableName);
        $form = 'edit';

        //se falhar
        if (isset($result['error']) == True){
            $form = 'new';
            //return the last inputed data to be autofilled
            $result = array_merge($result, $_POST);
        } else {
            $result['success'] = 'Registro inserido!';
        }
    }
} 


?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Projeto PHP Newtab Academy</title>
        <meta name="description" content="Projeto PHP Newtab Academy">
        <link rel="stylesheet" href="/css/styles.css">
    </head>
    <body>
        <div class="nav-menu">
            <div class="nav-item"><a href="/Clientes">Voltar</a></div>
        </div>


        <div class="content cliente-content">
            <div><?php echo $form == 'new' ? 'Cadastrar um Cliente' : 'Alterar um Cliente' ?></div>

            <form class="cliente-search-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                <div class="input-wrapper">
                    <label for="ID">ID do Cliente: </label>
                    <input type="text" readonly value="<?php echo isset($result['ID']) ? $result['ID'] : '' ?>" id="ID" name="ID" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="NomeCliente">Nome do Cliente: </label>
                    <input type="text" value="<?php echo isset($result['NomeCliente']) ? $result['NomeCliente'] : '' ?>" id="NomeCliente" name="NomeCliente" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="CPF">CPF</label>
                    <input type="text" value="<?php echo isset($result['CPF']) ? $result['CPF'] : '' ?>" id="CPF" name="CPF" maxlength="11" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="Email">E-mail: </label>
                    <input type="text" value="<?php echo isset($result['Email']) ? $result['Email'] : '' ?>" id="Email" name="Email" autocomplete="off">
                </div>

                <button title="<?php echo $form == 'new' ? 'Cadastrar' : 'Alterar' ?>" value="1" name="<?php echo $form == 'new' ? 'savenew' : 'save' ?>" formmethod="post" formaction="/Clientes/FormCliente.php"><?php echo $form == 'new' ? 'Salvar Novo Cliente' : 'Salvar Alterações' ?></button>

                <?php if($form == 'edit'){?>
                <button title="Cadastrar Outro Cliente" value="0" name="new" formmethod="get" formaction="/Clientes/FormCliente.php">Cadastrar Novo Cliente</button>
                <?php } ?>
            </form>

            <div class="msgs">
                <div class="successful">
                    <?php echo isset($result['success']) ? $result['success'] : '' ?>
                </div>
                <div class="errors">
                    <?php echo isset($result['errorInfo']) ? $result['errorInfo'][2] : '' ?>
                </div>
            </div>
        </div>

        <script src="/js/scripts.js"></script>
        <!-- bootstrap 5-->
        <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>