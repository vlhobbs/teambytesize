<?php

require 'vendor/autoload.php';

use scservice\SCConnect as Connect;

session_start();

if(!(isset($_SESSION)) ||!($_SESSION['valid'])){
    header("Location: login.php");
   exit();
}


?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Should-Cost Analysis: Product Search</title>
        <link rel="stylesheet" type="text/css" href="scstyle_01.css">
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
 
    </head>
    <body>
        <title>Product Search</title>
        <div class="sidebar">
<?php
    echo $_SESSION['username'];

//query database, store product names in array
//THIS is the part that needs changing
//Create script to only do this part after first (3) charaters are typed
try{
    $pdo = Connect::get()->connect();
    $pstm = $pdo->prepare('SELECT name FROM Products');
    $pstm->execute();
    $products = $pstm->fetchAll(PDO::FETCH_ASSOC);
	
    //Pass products array to javascript
    echo "<script language='javascript'> var productArray =" . json_encode($products) . "; </script>";
    echo "<script> console.log(JSON.stringify(productArray));</script>";
    
   } catch(\PDOException $e){
    echo "<script> window.alert('There was an exception found while attempting the autocomplete script.') </script>";
}
?>

            <br>
<!-- Dropdown Products -->
            <div class="dropdown">
                <button class="dropbutton">Products</button>
                <div class="dropcontent">
                    <a href="searchproduct.php">Product Search</a>
                    <a href="addproduct.php">Add Product</a>
                </div>
            </div>

<!-- Dropdown Commodities -->
            <div class="dropdown">
                <button class="dropbutton">Commodities</button>
                <div class="dropcontent">
                    <a href="searchcommodity.php">Commodity Search</a>
                    <a href="addcommodity.php">Add Commodity</a>
                </div>
            </div>

<!-- Hyperlinks -->
        
            <div class="dropdown">
                <a href="recentsearches.php"><button class="dropbutton">Recent Searches</button></a>
            </div>
            <div class="dropdown">
                <a href="logout.php"><button class="dropbutton">Logout</button></a>
            </div>
        </div>
        <br>

        <div class="main">
            <h1>Should-Cost Product Search</h1>
            <br>Search for the product below to view its should-cost total.

<?php
    //Set flag to enable searches to be stored in the database
    $_SESSION['issearch'] = true;

    if (isset($_SESSION['noterm'])){
        if ($_SESSION['noterm']){
            echo "<script type='text/javascript'>alert('Your search product is not in the database, please add the product or retry.');</script>";
            //echo $_SESSION['incorrectterm'];	
            //Reset flag to false after it returns the message
            unset($_SESSION['noterm']);
            unset($_SESSION['incorrectterm']);
        }
    }
?>
            <div class="noborder">
                <form action="materials.php"  autocomplete="off" method="get">
                    <label for="product">Product Name</label>
                    <input type="text" id ="product" name="product" onChange="createList()" placeholder="Enter Product to Search">
                    <br>
                    <button type="submit">Search</button>
                    <div id="completeContainer">
                    </div>
                </form>
            </div>
        </div>

 
        <script language="javascript">
            //I will have to break this one down and figure it out later
            //shorthand variables for html elements
            var input = document.getElementById("product");
            var cParent = document.getElementById("completeContainer");
            //console.log(productArray); //used to test array, don't need RN

            //Create a list of product suggestions when user types in search bar		
            input.addEventListener("input", function(e) {
       
                //Close all suggestions prior to creating new ones
                removeSuggest();
                if(input.value.length > 0) {

                     //Check if value in input matches any product names
                     for(var i = 0; i < productArray.length; i++) {
                
                         //Convert current product name to string
                         var name = JSON.stringify(productArray[i]["name"]);
                
                          name = name.replace(/^"(.*)"$/, '$1'); //strip double quotes from value

                          //Check for matches
                          if(input.value.toLowerCase() ===  name.substr(0, input.value.length).toLowerCase()) {
                              //Create suggestion on screen
                              var suggestion = document.createElement("div");
					
                              //Bold matching letters
                              suggestion.innerHTML = "<strong>" +  name.substr(0, input.value.length) + "</strong>";
                              suggestion.innerHTML += name.substr(input.value.length);

                              //Hide input field for value retrieval later
                              suggestion.innerHTML += "<input type='hidden' value='" + name + "'>";

                              //Add a border to separate one suggestion from the next
                              suggestion.style.border = "thin solid #000000";
                              suggestion.style.borderTop = "thin solid #FFFFFF";

                              //Change cursor to show item is clickable
                              suggestion.style.cursor = "pointer";

                              //Change input value to what ever user clicks
                              suggestion.addEventListener("click", function(e) {

                                  //Insert suggestion within input field
                                  input.value = this.getElementsByTagName("input")[0].value;
							
                                  //Stop showing suggestions after a selection
                                  removeSuggest();
                              });
                              cParent.appendChild(suggestion);
                          }
                      }
                  }
              });

              //Remove suggestions currently displayed on screen
              function removeSuggest(){
                  //The above code adds suggestions with child nodes so we see if we have any
                  //while loop doesn't seem to need brackets??
                  while(cParent.hasChildNodes())
                  //if so we methodically destroy all
                  cParent.removeChild(cParent.lastChild);
              }    

              //Remove suggestions when user clicks
              document.addEventListener("click", function(e){
                  removeSuggest();
              });
        </script>

    </body>
</html>

