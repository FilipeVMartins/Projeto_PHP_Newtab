<?php

require_once 'vendor/autoload.php';



///database config
$host = 'localhost';
$dbName = 'Projeto_PHP_Newtab';
$username = 'projetophpnewtab';
$password = 'Projeto$PHP1';

function createDbConnection(){
    Global $host;
    Global $dbName, $username, $password;
    try {
        $dbConnection = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $dbConnection;

    } catch (PDOException $e) {
        echo "</br>A conexão com o DB falhou: \n" . $e->getMessage() . "\n\n";
    }

    // finish connection
    $dbConnection = null;
}

function executeSelectDbQuery($sql){
    $STH = createDbConnection()->prepare($sql);
    $STH->execute();
    $result = $STH->fetchAll();
    return $result;
}



///create initial non-normalized model table
$initialTableName = 'tabela_inicial_pedido';

//check if table doesn't exist alrdy
$sqlCheckInitialTable = "SELECT COUNT(table_name) FROM information_schema.tables WHERE table_schema = '$dbName' AND table_name = '$initialTableName'";

foreach (createDbConnection()->query($sqlCheckInitialTable) as $row) {

    if ($row[0] == 0){
        //if Initial table doesn't exist, then create it
        $sqlCreateInitialTable = "CREATE TABLE `$dbName`.`$initialTableName` (
            `NumeroPedido` INT NOT NULL,
            `NomeCliente` VARCHAR(100) NOT NULL,
            `CPF` CHAR(11) NOT NULL,
            `Email` NCHAR(100) NULL, #10 characters max size was way too small
            `DtPedido` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `CodBarras` VARCHAR(20) NOT NULL,
            `NomeProduto` VARCHAR(100) NULL,
            `ValorUnitario` DECIMAL(15,2) NOT NULL,
            `Quantidade` INT NOT NULL
            )
        COMMENT = 'Tabela inicial com modelagem de dados desnormalizada';
        ";

        try {
            createDbConnection()->query($sqlCreateInitialTable);
            echo "</br>\nTabela inicial criada!\n\n";

        } catch (Exception $e) {
            echo "</br>\nHouve um erro ao criar a tabela inicial!\n\n";
            echo "</br>".$e;
        }

    } else {
        echo "</br>\nTabela inicial já existente!\n\n";
    }
}


///insert fake data into non-normalized model table
$faker = Faker\Factory::create('pt_BR');

//na tabela inicial `NumeroPedido` não é PK se cada pedido puder ter mais de um produto, tabela PedidoItem existe se cada pedido tiver mais de um item.
function generateFakeSqlInsert(){
    Global $faker;

    $nome = $faker->name;
    $cpf = str_replace(['.', '-'], '', $faker->cpf);
    $email = $faker->email;
    //Generate a timestamp using mt_rand.
    $timestamp = mt_rand(1, time());
    //Format that timestamp into a readable date string.
    $randomDate = date("Y-m-d H:i:s", $timestamp);
    $codBarras = $faker->numerify('#############');
    $nomeProduto = $faker->word;
    $valorUnitario = $faker->randomFloat($nbMaxDecimals = 2, $min = 1, $max = 1000);
    $quantidade = $faker->randomDigitNot(0);

    
    $TotalFakes = executeSelectDbQuery("SELECT COUNT(*) AS TotalFakes FROM `Projeto_PHP_Newtab`.`tabela_inicial_pedido`")[0]['TotalFakes'];
    $FakeRegistries = executeSelectDbQuery("SELECT * FROM `Projeto_PHP_Newtab`.`tabela_inicial_pedido`");
    $newNumeroPedido = (count($FakeRegistries) == 0 ? 1 : $FakeRegistries[count($FakeRegistries)-1]['NumeroPedido'] + 1);

    // generates everything new [clientes, produtos, pedidos] till 50 registries
    if ($TotalFakes < 50){
        return $sqlInsertInitialFakeData = "INSERT INTO `Projeto_PHP_Newtab`.`tabela_inicial_pedido` (`NumeroPedido`, `NomeCliente`, `CPF`, `Email`, `DtPedido`, `CodBarras`, `NomeProduto`, `ValorUnitario`, `Quantidade`)
                                            VALUES ('$newNumeroPedido', '$nome', '$cpf', '$email', '$randomDate', '$codBarras', '$nomeProduto', $valorUnitario, $quantidade);";

    // generates new [pedidos, produtos] till 200 registries, it will randomly reuse previous clientes data (NomeCliente, CPF, Email) registered at the first 50 registries
    } elseif ($TotalFakes < 200){
        $reuseRandomCliente = $FakeRegistries[rand(1,50)];

        return $sqlInsertInitialFakeData = "INSERT INTO `Projeto_PHP_Newtab`.`tabela_inicial_pedido` (`NumeroPedido`, `NomeCliente`, `CPF`, `Email`, `DtPedido`, `CodBarras`, `NomeProduto`, `ValorUnitario`, `Quantidade`)
                                            VALUES ('$newNumeroPedido', '".$reuseRandomCliente['NomeCliente']."', '".$reuseRandomCliente['CPF']."', '".$reuseRandomCliente['Email']."', '$randomDate', '$codBarras', '$nomeProduto', $valorUnitario, $quantidade);";

    //generates only new [produtos] above 200 registries, it will randomly reuse previous clientes data (NomeCliente, CPF, Email) and pedidos (NumeroPedido, DtPedido) registered at the first 200 registries
    } elseif ($TotalFakes >= 200){
        $reuseRandomPedidoCliente = $FakeRegistries[rand(1,200)];

        return $sqlInsertInitialFakeData = "INSERT INTO `Projeto_PHP_Newtab`.`tabela_inicial_pedido` (`NumeroPedido`, `NomeCliente`, `CPF`, `Email`, `DtPedido`, `CodBarras`, `NomeProduto`, `ValorUnitario`, `Quantidade`)
                                            VALUES ('".$reuseRandomPedidoCliente['NumeroPedido']."', '".$reuseRandomPedidoCliente['NomeCliente']."', '".$reuseRandomPedidoCliente['CPF']."', '".$reuseRandomPedidoCliente['Email']."', '".$reuseRandomPedidoCliente['DtPedido']."', '$codBarras', '$nomeProduto', $valorUnitario, $quantidade);";

    }
    //insert para caso de cada pedido ter apenas um produto
    // return $sqlInsertInitialFakeData = "INSERT INTO `Projeto_PHP_Newtab`.`tabela_inicial_pedido` (`NomeCliente`, `CPF`, `Email`, `DtPedido`, `CodBarras`, `NomeProduto`, `ValorUnitario`, `Quantidade`)
    //                                     VALUES ('$nome', '$cpf', '$email', '$randomDate', '$codBarras', '$nomeProduto', $valorUnitario, $quantidade);";
}

// check how many fake registries data there are alrdy in the db
$sqlCheckDataInitialTable = "SELECT COUNT(NumeroPedido) FROM Projeto_PHP_Newtab.tabela_inicial_pedido";

foreach (createDbConnection()->query($sqlCheckDataInitialTable) as $row) {
    $maxFakes = 500;

    if ($row[0] < $maxFakes){
        // fill up till $maxFakes
        for ($x = 1; $x <= $maxFakes-$row[0]; $x++) {
            try {
                $result = createDbConnection()->query(generateFakeSqlInsert());
            } catch (Exception $e) {
                //echo "Houve um erro na inserção de fake data: \n" . $e->getMessage() . "\n\n";
                $x--;
            }
        };
        echo "</br>\n Adicionado " . $maxFakes-$row[0] . " registros para teste.\n\n";
    }
    echo "</br>\n Nenhum registro de teste foi adicionado, $row[0] registros encontrados.\n";
}



///create normalized model tables

$clienteTableName = 'Cliente';
//check if table Cliente doesn't exist alrdy
$sqlCheckClienteTable = "SELECT COUNT(table_name) FROM information_schema.tables WHERE table_schema = '$dbName' AND table_name = '$clienteTableName'";

foreach (createDbConnection()->query($sqlCheckClienteTable) as $row) {

    if ($row[0] == 0){
        //if Cliente table doesn't exist, then create it
        $sqlCreateClienteTable = "CREATE TABLE `Projeto_PHP_Newtab`.`Cliente` (
            `ID` INT NOT NULL AUTO_INCREMENT,
            `NomeCliente` VARCHAR(100) NOT NULL,
            `CPF` VARCHAR(11) NOT NULL,
            `Email` VARCHAR(45) NULL,
            PRIMARY KEY (`ID`),
            UNIQUE INDEX `id_UNIQUE` (`ID` ASC) VISIBLE,
            UNIQUE INDEX `CPF_UNIQUE` (`CPF` ASC) VISIBLE)
          COMMENT = 'Tabela para armazenar dados cadastrais de clientes.';
        ";

        try {
            createDbConnection()->query($sqlCreateClienteTable);
            echo "</br>\nTabela $clienteTableName criada!\n";

        } catch (Exception $e) {
            echo "</br>\nHouve um erro ao criar a tabela $clienteTableName!\n";
        }

    } else {
        echo "</br>\nTabela $clienteTableName já existente!\n";
    }
}


$produtoTableName = 'Produto';
//check if table Produto doesn't exist alrdy
$sqlCheckProdutoTable = "SELECT COUNT(table_name) FROM information_schema.tables WHERE table_schema = '$dbName' AND table_name = '$produtoTableName'";

foreach (createDbConnection()->query($sqlCheckProdutoTable) as $row) {

    if ($row[0] == 0){
        //if Produto table doesn't exist, then create it
        $sqlCreateProdutoTable = "CREATE TABLE `Projeto_PHP_Newtab`.`Produto` (
            `ID` INT NOT NULL AUTO_INCREMENT,
            `CodBarras` VARCHAR(20) NOT NULL,
            `NomeProduto` VARCHAR(100) NULL,
            `ValorUnitario` DECIMAL(15,2) NOT NULL,
            PRIMARY KEY (`ID`),
            UNIQUE INDEX `ID_UNIQUE` (`ID` ASC) VISIBLE,
            UNIQUE INDEX `CodBarras_UNIQUE` (`CodBarras` ASC) VISIBLE)
          COMMENT = 'Tabela para armazenar dados cadastrais de produtos.';
        ";

        try {
            createDbConnection()->query($sqlCreateProdutoTable);
            echo "</br>\nTabela $produtoTableName criada!\n";

        } catch (Exception $e) {
            echo "</br>\nHouve um erro ao criar a tabela $produtoTableName!\n";
        }

    } else {
        echo "</br>\nTabela $produtoTableName já existente!\n";
    }
}


$pedidoTableName = 'Pedido';
//check if table Pedido doesn't exist alrdy
$sqlCheckPedidoTable = "SELECT COUNT(table_name) FROM information_schema.tables WHERE table_schema = '$dbName' AND table_name = '$pedidoTableName'";

foreach (createDbConnection()->query($sqlCheckPedidoTable) as $row) {

    if ($row[0] == 0){
        //if Pedido table doesn't exist, then create it
        $sqlCreatePedidoTable = "CREATE TABLE `Projeto_PHP_Newtab`.`Pedido` (
            `NumeroPedido` INT NOT NULL AUTO_INCREMENT,
            `ID_Cliente` INT NOT NULL,
            `DtPedido` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `Pedidocol` VARCHAR(45) NULL,
            PRIMARY KEY (`NumeroPedido`),
            UNIQUE INDEX `idnew_table_UNIQUE` (`NumeroPedido` ASC) VISIBLE,
            INDEX `ID_idx` (`ID_Cliente` ASC) VISIBLE,
            CONSTRAINT `ID_Cliente`
              FOREIGN KEY (`ID_Cliente`)
              REFERENCES `Projeto_PHP_Newtab`.`Cliente` (`ID`)
              ON DELETE NO ACTION
              ON UPDATE NO ACTION)
          COMMENT = 'Tabela para armazenar dados cadastrais de pedidos.';
        ";

        try {
            createDbConnection()->query($sqlCreatePedidoTable);
            echo "</br>\nTabela $pedidoTableName criada!\n";

        } catch (Exception $e) {
            echo "</br>\nHouve um erro ao criar a tabela $pedidoTableName!\n";
        }

    } else {
        echo "</br>\nTabela $pedidoTableName já existente!\n";
    }
}


$pedidoItemTableName = 'PedidoItem';
//check if table PedidoItem doesn't exist alrdy
$sqlCheckPedidoItemTable = "SELECT COUNT(table_name) FROM information_schema.tables WHERE table_schema = '$dbName' AND table_name = '$pedidoItemTableName'";

foreach (createDbConnection()->query($sqlCheckPedidoItemTable) as $row) {

    if ($row[0] == 0){
        //if PedidoItem table doesn't exist, then create it
        $sqlCreatePedidoItemTable = "CREATE TABLE `Projeto_PHP_Newtab`.`PedidoItem` (
            `ID` INT NOT NULL AUTO_INCREMENT,
            `ID_NumeroPedido` INT NOT NULL,
            `ID_Produto` INT NOT NULL,
            `Quantidade` INT NOT NULL,
            INDEX `ID_NumeroPedido_idx` (`ID_NumeroPedido` ASC) VISIBLE,
            INDEX `ID_Produto_idx` (`ID_Produto` ASC) VISIBLE,
            PRIMARY KEY (`ID`),
            UNIQUE INDEX `ID_UNIQUE` (`ID` ASC) VISIBLE,
            CONSTRAINT `ID_NumeroPedido`
              FOREIGN KEY (`ID_NumeroPedido`)
              REFERENCES `Projeto_PHP_Newtab`.`Pedido` (`NumeroPedido`)
              ON DELETE NO ACTION
              ON UPDATE NO ACTION,
            CONSTRAINT `ID_Produto`
              FOREIGN KEY (`ID_Produto`)
              REFERENCES `Projeto_PHP_Newtab`.`Produto` (`ID`)
              ON DELETE NO ACTION
              ON UPDATE NO ACTION)
          COMMENT = 'Tabela para armazenar detalhes de itens cadastrados por pedido.';
          
        ";

        try {
            createDbConnection()->query($sqlCreatePedidoItemTable);
            echo "</br>\nTabela $pedidoItemTableName criada!\n";

        } catch (Exception $e) {
            echo "</br>\nHouve um erro ao criar a tabela $pedidoItemTableName!\n";
        }

    } else {
        echo "</br>\nTabela $pedidoItemTableName já existente!\n";
    }
}



///migrate data from non-normalized model into normalized model tables

$sqlSelectInitialTable = "SELECT * FROM Projeto_PHP_Newtab.tabela_inicial_pedido";
//bring all data from initial table to be migrated to normalized tables
foreach (createDbConnection()->query($sqlSelectInitialTable) as $row) {

    try {
        //check if the current cliente in the $row is alrdy registered on normalized tables
        $cpf = $row['CPF'];
        $sqlCheckClientExist = "SELECT ID FROM Projeto_PHP_Newtab.Cliente WHERE CPF = '$cpf'";

        $arrayClientesNormalized = executeSelectDbQuery($sqlCheckClientExist);
        if(count($arrayClientesNormalized) != 0){
            //if it is, return its ID
            $ClienteNormalizedID = $arrayClientesNormalized[0]['ID'];
        } else {
            $nome = $row['NomeCliente'];
            $email = $row['Email'];
            //if it's not, then register it and return its ID
            $sqlInsertNormalizedCliente = "INSERT INTO `Projeto_PHP_Newtab`.`Cliente` (`NomeCliente`, `CPF`, `Email`)
                                            VALUES ('$nome', '$cpf', '$email');";
            $db = createDbConnection();
            $insertCliente = $db->prepare($sqlInsertNormalizedCliente);
            $insertCliente->execute();
            $ClienteNormalizedID = $db->lastInsertId();
        }

    } catch (Exception $e) {
        echo "$e";
    }

    
    
    

    



    echo'</br>';
    print_r($row);
    echo'</br>';
}







?>