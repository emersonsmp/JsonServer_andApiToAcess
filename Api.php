<?php
//SERVIDOR

//COMO CHAMAR VIA URL
//http://localhost/api/api.php?path=series/id
//http://localhost/api/api.php?path=filmes/id

//(1) $_GET: 
//SE DIGITADO CORRETAMENTE IRA GUARDAR "series/id"


//IF PARA VERIFICAR SE ESTA SEM PATH:
if(!array_key_exists('path', $_GET)){
	echo 'ERROR, Path não encontrado.';
	exit;
}

// Cria um array de parâmetros {$path[0]=series, $path[1]=1 }
$path= explode('/', $_GET['path']);


//VERIFICA SE O PATH ESTÁ VAZIO
if(count($path)== 0 || $path[0] == "" ){
	echo 'ERROR, Path vazio.';
	exit;
}

//SE FOR MAIOR QUE 1 CONTEM PARAMETROS;
//ENTAO $param = $path[1];
//PARAMETROS NA URL ex: path=series"/1"
$param = "";
if(count($path)>1){
	$param = $path[1];
}

//RETORNA A LEITURA DO ARQUIVO EM STRING;
$contents = file_get_contents('data.json');


//CONVERSAO $contents(string) PARA JSON;
$json = json_decode($contents, true);


//ARRAY, COM DIVERSAS INFORMACOES
//VAI RECEBER O METODO EX: GET, POST, DELETE;
$method = $_SERVER['REQUEST_METHOD'];

//PARA DESCREVER O QUE IRA SERVIR NA RESPOSTA;
header('Content-type: application/json');
//GUARDA O DADOS DO METHOD POST
$body = file_get_contents('php://input');


//RECEBE UM "VETOR" E O PARAMETRO EX: 1, 2
//PERCORRE TODAS AS KEYS DO VETOR {"VETOR"[{"ID":"1"},{"ID":"2"},{"ID":"3"}]}
//COLOCA EM $OBJ, E VERIFICA SE $OBJ["ID"] == $PARAML, E RETURNA A $KEY
function findById($vector, $param1){
    $encontrado = -1;
    foreach($vector as $key => $obj){          
      if($obj['id'] == $param1){
        $encontrado = $key;
        break;
      }
    }
    return $encontrado;
}


///////////////////////////////////
// $path[0] = "SERIES", "FILMES";//
// $path[1] = ID (EX: 1,2,3...);///
// $body RECEBE ('php://input');///
///////////////////////////////////

//////////////////////////////////METODO GET//////////////////////////////////////

if($method === 'GET'){
	
	if($json[$path[0]]){//VERFICA SE PATH[0] EXISTE:
		if($param==""){//IF NÃO TEM PARAMETRO 
			echo json_encode($json[$path[0]]);//PRINTA TUDO
		
		}else{//IF POSSUI PARAMETRO
			$encontrado = -1;
			
			//PROCURA CADA $KEY DO VETOR COMPARANDO A ID
			//SE ENCONTRAR SALVA EM $ENCONTRADO
			foreach($json[$path[0]] as $key => $obj){
				if($obj['id'] == $param){
					$encontrado = $key;
					break;
				}
			}
			
			//IF ENCONTROU ALGO
			if($encontrado >=0){
				//PRINTA A KEY ENCONTRADA EX: {"ID":"1", "NAME":"FLASH", "ANO":"2015"}
				echo json_encode($json[$path[0]][$encontrado]);
			}else{
				echo '404 Elemento não encontrado! ';
			}
		}
	}else{
		echo '[]';
	}
}


/////////////////////////////////////////METODO POST///////////////////////////////////////////////

if($method === 'POST'){
	
	//$body RECEBE ('php://input');
	$jsonBody = json_decode($body, true);
	$jsonBody['id'] = time();
	
	//CASO NÃO EXISTA PATH[0], CRIE!
	
	if(!$json[$path[0]]){
		$json[$path[0]] = [];
	}	
	
    //ESCREVE NO ARQUIVO	
	$json[$path[0]][] = $jsonBody;
	echo json_encode($jsonBody);
	file_put_contents('data.json', json_encode($json));
	
}

//////////////////////////////////////////DELETE//////////////////////////////////////////////////////////

if($method === 'DELETE'){
	
    if($json[$path[0]]){
      if($param==""){
        echo 'error';
      }else{
		  
		//VAI PROCURAR A KEY NO PATH[0]  
        $encontrado = findById($json[$path[0]], $param);
		//SE ENCONTROU:
        if($encontrado>=0){
          echo json_encode($json[$path[0]][$encontrado]);
		  
		  //DELETA A KEY, E ESCREVE NO ARQUIVO;
          unset($json[$path[0]][$encontrado]);
          file_put_contents('data.json', json_encode($json));
        }else{//NAO ENCONTROU A KEY
          echo 'ERROR.';
          exit;
        }
      }
    }else{//PATH NAO EXISTE (EX: "NOVELAS");
      echo 'error.';
    }
  }
  
///////////////////////////////////////////////PUT/////////////////////////////////////////////////////////
  if($method === 'PUT'){
	  
    if($json[$path[0]]){
      if($param==""){
        echo 'error';
      }else{
        $encontrado = findById($json[$path[0]], $param);
		
        if($encontrado>=0){
		  //PEGA INPUT, COLOCA O MSM $PARAM
          $jsonBody = json_decode($body, true);
          $jsonBody['id'] = $param;
		  
		  //SOBRESCREVE A KEY, E ATUALIZA ARQUIVO
          $json[$path[0]][$encontrado] = $jsonBody;
          echo json_encode($json[$path[0]][$encontrado]);
          file_put_contents('data.json', json_encode($json));
        }else{
          echo 'ERROR.';
          exit;
        }
      }
    }else{
      echo 'error.';
    }
  }

?>
