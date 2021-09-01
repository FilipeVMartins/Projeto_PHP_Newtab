<?php
require_once '../dbFunctions/dbConnection.php';

//print_r($_POST);
//print_r($_GET);
$tableName = 'Produto';
$form = 'new';
if ($_GET){
    
    if (isset($_GET['edit'])){
        $form = 'edit';
        $result = executeSelectByID($_GET['edit'], 'ID', $tableName)[0];
    }

} elseif ($_POST) {
    $form = 'edit';

    //update
    if (isset($_POST['save'])){
        //after save, return $result if successful
        $result = executeUpdateByID($_POST['ID'], 'ID', $tableName);

        //se falhar
        if (isset($result['error']) == True){
            //return the last inputed data to be autofilled
            $result = array_merge($result, $_POST);
        } else {
            $result['success'] = 'Registro alterado!';
        }

    //insert
    } elseif (isset($_POST['savenew'])){

        $result = executeInsert('ID', $tableName);
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
            <div class="nav-item"><a href="/Produtos">Voltar</a></div>
        </div>


        <div class="content produto-content">
            <div><?php echo $form == 'new' ? 'Cadastrar um Produto' : 'Alterar um Produto' ?></div>

            <form class="produto-search-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                <div class="input-wrapper">
                    <label for="ID">ID do Produto: </label>
                    <input type="text" readonly value="<?php echo isset($result['ID']) ? $result['ID'] : '' ?>" id="ID" name="ID" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="NomeProduto">Nome do Produto: </label>
                    <input type="text" value="<?php echo isset($result['NomeProduto']) ? $result['NomeProduto'] : '' ?>" id="NomeProduto" name="NomeProduto" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="CodBarras">Código de Barras: </label>
                    <input type="text" value="<?php echo isset($result['CodBarras']) ? $result['CodBarras'] : '' ?>" id="CodBarras" name="CodBarras" maxlength="20" autocomplete="off">
                </div>

                <div class="input-wrapper">
                    <label for="ValorUnitario">Valor Unitário: </label>
                    <input type="text" value="<?php echo isset($result['ValorUnitario']) ? $result['ValorUnitario'] : '' ?>" id="ValorUnitario" name="ValorUnitario" autocomplete="off">
                </div>

                <button title="<?php echo $form == 'new' ? 'Cadastrar' : 'Alterar' ?>" value="1" name="<?php echo $form == 'new' ? 'savenew' : 'save' ?>" formmethod="post" formaction="/Produtos/FormProduto.php"><?php echo $form == 'new' ? 'Salvar Novo Produto' : 'Salvar Alterações' ?></button>

                <?php if($form == 'edit'){?>
                <button title="Cadastrar Outro Produto" value="0" name="new" formmethod="get" formaction="/Produtos/FormProduto.php">Cadastrar Novo Produto</button>
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