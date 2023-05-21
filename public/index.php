<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/db.php';

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

// GET REQUEST
$app->get('/customer', function (Request $request, Response $response, array $args) {
    $db = new DB();
    $sql = "SELECT * FROM tblcustomer";
    //Connect Database
    $connect = $db->connect();
    //Execute Query
    $result = mysqli_query($connect, $sql);

    //Check number of row
    if (mysqli_num_rows($result) > 0) {
        // Fetch each data (mysqli_fetch_all get assosiative and value array)
        while($row = mysqli_fetch_all($result, MYSQLI_ASSOC)) {
            $response->getBody()->write(json_encode($row));
        }
    } 
    else {
        $response->getBody()->write("0 results");
    }
    mysqli_free_result($result);
    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});

// GET REQUEST (ONE DATA)
$app->get('/customer/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $db = new DB();
    $sql = "SELECT * FROM tblcustomer WHERE id = $id";
    //Connect Database
    $connect = $db->connect();
    //Execute Query
    $result = mysqli_query($connect, $sql);

    //Check number of row
    if (mysqli_num_rows($result) > 0) {
        // Fetch each data (mysqli_fetch_all get assosiative and value array)
        while($row = mysqli_fetch_all($result, MYSQLI_ASSOC)) {
            $response->getBody()->write(json_encode($row));
        }
    } 
    else {
        $response->getBody()->write("0 results");
    }
    mysqli_free_result($result);
    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});

// POST REQUEST (ADD DATA)
$app->post('/customer/add', function (Request $request, Response $response, array $args) {
    //Get All Data of JSON from request users
    $data = $request->getBody();
    $value = json_decode($data, TRUE); // Convert to JSON

    $db = new DB();
    $sql = "INSERT INTO tblcustomer (first_name, last_name, add_ress) VALUES (?,?,?)";

    //Connect Database
    $connect = $db->connect();
    
    //Execute Query
    if($stmt = mysqli_prepare($connect, $sql)){
        mysqli_stmt_bind_param($stmt, "sss", $first_name, $last_name, $add_ress);
        
        $first_name = $value['first_name'];  
        $last_name = $value['last_name']; 
        $add_ress =  $value['add_ress'];      
        mysqli_stmt_execute($stmt);
        $response->getBody()->write("Record Added Successfully");
    }
    else{
        $response->getBody()->write("Error: Could not prepare query");
    }
    
    mysqli_stmt_close($stmt);
    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});

// PUT REQUEST (UPDATE DATA)
$app->put('/customer/edit/{id}', function (Request $request, Response $response, array $args) {
    $sql = null;

    $id = $request->getAttribute('id');   
    $data = $request->getBody();
    $value = json_decode($data, TRUE);

    //Execute Query
    if(!(empty($id))){
        $first_name = $value['first_name'];  
        $last_name = $value['last_name']; 
        $add_ress =  $value['add_ress'];      
        $sql = "UPDATE tblcustomer SET first_name = '$first_name', last_name = '$last_name', add_ress ='$add_ress' WHERE id = " . $id;
    }
    else{
        die("Error: ID not Define");
    }

    $db = new DB();

    //Connect Database
    $connect = $db->connect();

    if (mysqli_query($connect, $sql)) {
        $response->getBody()->write("Record Update Successfully");
    } 
    else {
        $response->getBody()->write("Error: Update Record");
    }

    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});

// DELETE REQUEST (UPDATE DATA)
$app->delete('/customer/delete/{id}', function (Request $request, Response $response, array $args) {
    $sql = null;

    $id = $request->getAttribute('id');   
    $data = $request->getBody();
    $value = json_decode($data, TRUE);

    //Execute Query
    if(!(empty($id))){ 
        $sql = "DELETE FROM tblcustomer WHERE id = " . $id;
    }
    else{
        die("Error: ID not Define");
    }

    $db = new DB();

    //Connect Database
    $connect = $db->connect();

    if (mysqli_query($connect, $sql)) {
        $response->getBody()->write("Record Delete Successfully");
    } 
    else {
        $response->getBody()->write("Error: Delete Record");
    }

    $db->closeConnection($connect);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
});

$app->run();