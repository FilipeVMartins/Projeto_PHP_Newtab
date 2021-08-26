<?php


//database
$host = 'localhost';
$dbName = 'Projeto_PHP_Newtab';
$username = 'projetophpnewtab';
$password = 'Projeto$PHP1';

function dbExecuteQuerie($sql){
    Global $host;
    Global $dbName, $username, $password;
    try {
        $dbConnection = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConnection->query($sql);

    } catch (PDOException $e) {
        echo "Connection failed: \n" . $e->getMessage() . "\n";
    }

    // finish connection
    $dbConnection = null;
}


//create initial non-normalized model table
$initialTableName = 'tabela_inicial_pedido';

//check if table doesn't exist alrdy
$sqlCheckInitialTable = "SELECT table_name FROM information_schema.tables WHERE table_schema = '$dbName' AND table_name = '$initialTableName'";

if (dbExecuteQuerie($sqlCheckInitialTable)->rowCount() == 0){

    //if Initial table doesn't exist, then create it
    $sqlCreateInitialTable = "
    CREATE TABLE `$dbName`.`$initialTableName` (
        `NumeroPedido` INT NOT NULL,
        `NomeCliente` VARCHAR(100) NOT NULL,
        `CPF` CHAR(11) NOT NULL,
        `Email` NCHAR(10) NULL,
        `DtPedido` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `CodBarras` VARCHAR(20) NOT NULL,
        `NomeProduto` VARCHAR(100) NULL,
        `ValorUnitario` DECIMAL(15,2) NOT NULL,
        `Quantidade` INT NOT NULL,
        PRIMARY KEY (`NumeroPedido`))
      COMMENT = 'Tabela inicial com modelagem de dados desnormalizada';
    ";

    $result = dbExecuteQuerie($sqlCreateInitialTable);

    echo "\nTabela inicial criada!\n";
} else {
    echo "\nTabela inicial já existente!\n";
}


//insert fake data into non-normalized model table


//create normalized model tables


//migrate data from non-normalized model into normalized model tables










?>