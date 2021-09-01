<?php

// 1- create the database 'Projeto_PHP_Newtab', 2- create the db user 'projetophpnewtab', 3- grant the created user 'projetophpnewtab' the necessary permissions.
///database config,
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
        echo "</br>A conex�o com o DB falhou: \n" . $e->getMessage() . "\n\n";
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

function executeSelectDbQueryUserInput($tableName){
    $tableColumns = getTableColumns($tableName);

    //maintain previous request form data
    $qtd_paginacao = $_GET['qtd_paginacao'];
    $offset_atual = $_GET['offset_atual'];
    $ordernar_campo = $_GET['ordernar_campo'];
    $ordernar_tipo = $_GET['ordernar_tipo'];

    //sql constructor
    $sqlSearch = "SELECT * FROM Projeto_PHP_Newtab.$tableName WHERE TRUE AND";
    //fields and values to be searched
    $arrayInsertedValues = [];
    
    //it will search LIKE only on non-empty fields AND on the _GET fields that exists on column names
    foreach ($_GET as $key => $value){
        $isTableField = in_array($key, $tableColumns);
        if ($isTableField && $value){
            $sqlSearch .= " ($key LIKE :$key) AND";
            $arrayInsertedValues[$key] = $value;
        }
    }

    //remove last 'AND'
    if(substr($sqlSearch, -3) == "AND"){
        $sqlSearch = substr($sqlSearch, 0, -3);
    }

    $sqlSearchNotPaginated = $sqlSearch;

    //add order by
    $sqlSearch .= "ORDER BY $ordernar_campo $ordernar_tipo ";
    //add limit and offset
    $sqlSearch .= "LIMIT $qtd_paginacao OFFSET $offset_atual";

    //execute query
    $sth = createDbConnection()->prepare($sqlSearch);
    $sth2 = createDbConnection()->prepare($sqlSearchNotPaginated);

    foreach ($arrayInsertedValues as $key => $value){
        $sth->bindValue(":$key", "%{$value}%");
        $sth2->bindValue(":$key", "%{$value}%");
    }

    try {
        $sth->execute();
        $sth2->execute();
    } catch (PDOException $e){
        //if any error occurs then return empty string
        return [];
    }
    
    $result = $sth->fetchAll();
    $rowCountResult = $sth2->rowCount();
    return ['result' => $result, 'rowCount' => $rowCountResult];
}


function getTableColumns($tableName){
    $sqlTableColumns = "SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` 
                        WHERE `TABLE_SCHEMA`='Projeto_PHP_Newtab' AND `TABLE_NAME`='$tableName'";

    $result = [];
    foreach(executeSelectDbQuery($sqlTableColumns) as $columnName){
        $result[] = $columnName['COLUMN_NAME'];
    }
    return $result;     
}

function executeDeleteDbQueryUserInput($tableName, $id){
    
    $sqlDelete = "DELETE FROM Projeto_PHP_Newtab.$tableName WHERE ID = :id";
    $sth = createDbConnection()->prepare($sqlDelete);
    $sth->bindValue(":id", "$id");

    try {
        $sth->execute();
        return "O $tableName com ID:$id foi excluído! Juntamente com todos os registros a ele associados.";
    } catch (PDOException $e){
        //if any error occurs then return empty string
        return "ocorreu um erro ao excluir o $tableName : " . $e;
    }
}

function executeSelectByID ($id, $idKey, $table){
    $sqlSelectByID = "SELECT * FROM `Projeto_PHP_Newtab`.`$table` WHERE $idKey = :id";
    $sth = createDbConnection()->prepare($sqlSelectByID);
    $sth->bindValue(":id", "$id");
    
    try {
        $sth->execute();
    } catch (PDOException $e){
        //if any error occurs then return it
        return $e;
    }
    
    $result = $sth->fetchAll();
    return $result;
}

function executeUpdateByID ($id, $idKey, $table){
    //existing table columns
    $tableColumns = getTableColumns($table);

    //sql build
    $sqlUpdateByID = "UPDATE `Projeto_PHP_Newtab`.`$table` SET ";
    
    foreach ($_POST as $key => $value){
        $isTableField = in_array($key, $tableColumns);

        if ($isTableField && $value && $key != $idKey){
            $sqlUpdateByID .= "$key = :$key, ";
        }
    }
    //remove last ','
    if(substr($sqlUpdateByID, -2) == ", "){
        $sqlUpdateByID = substr($sqlUpdateByID, 0, -2);
    }
    $sqlUpdateByID .= " WHERE $idKey = :$idKey";
    

    //prepare and bind values
    $sth = createDbConnection()->prepare($sqlUpdateByID);
    $sth->bindValue(":$idKey", "$id");

    foreach ($_POST as $key => $value){
        $isTableField = in_array($key, $tableColumns);

        if ($isTableField && $value && $key != $idKey){
            $sth->bindValue(":$key", "$value");
        }
    }

    //execute query
    try {
        $sth->execute();
    } catch (PDOException $e){
        //if any error occurs then return empty string
        return ['error'=>True, 'errorInfo' => $e->errorInfo];
    }
    
    //return updated registry
    return executeSelectByID($id, $idKey, $table)[0];
}

function executeInsert ($idKey, $table){
    //existing table columns
    $tableColumns = getTableColumns($table);

    //sql build
    $sqlInsert = "INSERT INTO `Projeto_PHP_Newtab`.`$table` (";//`NomeCliente`, `CPF`, `Email`) VALUES ('$nome', '$cpf', '$email');";
    $sqlInsertValues = " VALUES (";

    foreach ($_POST as $key => $value){
        $isTableField = in_array($key, $tableColumns);

        if ($isTableField && $value && $key != $idKey){
            $sqlInsert .= "`$key`, ";
            $sqlInsertValues .= ":$key, ";
        }
    }
    //remove last ','
    if(substr($sqlInsert, -2) == ", "){
        $sqlInsert = substr($sqlInsert, 0, -2);
        $sqlInsert .= ')';
    }
    //remove last ','
    if(substr($sqlInsertValues, -2) == ", "){
        $sqlInsertValues = substr($sqlInsertValues, 0, -2);
        $sqlInsertValues .= ')';
    }
    $sqlInsert .= $sqlInsertValues;

    //prepare and bind values
    $db= createDbConnection();
    $sth = $db->prepare($sqlInsert);

    foreach ($_POST as $key => $value){
        $isTableField = in_array($key, $tableColumns);

        if ($isTableField && $value && $key != $idKey){
            $sth->bindValue(":$key", "$value");
        }
    }

    //execute query
    try {
        $sth->execute();
    } catch (PDOException $e){
        //if any error occurs then return it
        return ['error'=>True, 'errorInfo' => $e->errorInfo];
    }

    //return updated registry
    return executeSelectByID($db->lastInsertId(), $idKey, $table)[0];
}

