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
        echo "A conexão com o DB falhou: \n" . $e->getMessage() . "\n\n";
    }

    // finish connection
    $dbConnection = null;
}



///create initial non-normalized model table
$initialTableName = 'tabela_inicial_pedido';

//check if table doesn't exist alrdy
$sqlCheckInitialTable = "SELECT COUNT(table_name) FROM information_schema.tables WHERE table_schema = '$dbName' AND table_name = '$initialTableName'";

foreach (createDbConnection()->query($sqlCheckInitialTable) as $row) {

    if ($row[0] == 0){
        //if Initial table doesn't exist, then create it
        $sqlCreateInitialTable = "CREATE TABLE `$dbName`.`$initialTableName` (
            `NumeroPedido` INT NOT NULL AUTO_INCREMENT,
            `NomeCliente` VARCHAR(100) NOT NULL,
            `CPF` CHAR(11) NOT NULL,
            `Email` NCHAR(100) NULL, #10 characters max size was way too small
            `DtPedido` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `CodBarras` VARCHAR(20) NOT NULL,
            `NomeProduto` VARCHAR(100) NULL,
            `ValorUnitario` DECIMAL(15,2) NOT NULL,
            `Quantidade` INT NOT NULL,
            PRIMARY KEY (`NumeroPedido`))
        COMMENT = 'Tabela inicial com modelagem de dados desnormalizada';
        ";

        createDbConnection()->query($sqlCreateInitialTable);

        echo "\nTabela inicial criada!\n\n";
    } else {
        echo "\nTabela inicial já existente!\n\n";
    }
}



///insert fake data into non-normalized model table
$faker = Faker\Factory::create('pt_BR');

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

    return $sqlInsertInitialFakeData = "INSERT INTO `Projeto_PHP_Newtab`.`tabela_inicial_pedido` (`NomeCliente`, `CPF`, `Email`, `DtPedido`, `CodBarras`, `NomeProduto`, `ValorUnitario`, `Quantidade`)
                                        VALUES ('$nome', '$cpf', '$email', '$randomDate', '$codBarras', '$nomeProduto', $valorUnitario, $quantidade);";
}

// check how many fake registries data there are alrdy in the db
$sqlCheckDataInitialTable = "SELECT COUNT(NumeroPedido) FROM Projeto_PHP_Newtab.tabela_inicial_pedido";

foreach (createDbConnection()->query($sqlCheckDataInitialTable) as $row) {
    $maxFakes = 700;

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
        
    }
}



///create normalized model tables








///migrate data from non-normalized model into normalized model tables










?>