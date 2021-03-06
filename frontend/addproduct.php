<?php
//addproduct.php

//Converted to PDO, currently working
//Consider moving all Javascript to its own file and then using an include statement
//Also may need to set all PDO statements to null after completion
//Which would be true for all files so I'll research first, then do all at once

require 'vendor/autoload.php';
use scservice\SCConnect as Connect;

session_start();

if (!isset($_SESSION['valid'])){
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Add Product</title>
        <link rel="stylesheet" type="text/css" href="scstyle_01.css">
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    </head>


    <body>  
        <div class="sidebar">
<?php
    echo $_SESSION['username']; 
    include 'sidebar.php';
?>
        </div>

        <div class="main">

            <h1>Add Product</h1>

            <form name="newproduct" action="addproduct.php" autocomplete="off" method="POST">

            <div id="productDetails" class="noborder">
                <input type="text" name="productname" placeholder="Enter Product Name" required/>
                <br>

         
<script language="javascript">
    var commodityCount = 0;

    //Adds a new commodity field to the form
    function addCommodity() {
        commodityCount++; //increment counter for new id
        //Add an input field for the commodity name
        var html = '<input type="text" name="commodities[]" placeholder="Commodity Name"required/>'
        //Add an input field for the commodity price
        html += '<input type="number" min="0" step="0.01" name="prices[]" placeholder="Price in USD"required/>';
        //Add an input field for the commodity unit
        html += '<input type="text" name="units[]" placeholder="Weight Unit"required/>';
        //Add an input field for the commodity weight
        html += '<input type="number" min="0" step="0.01" name="weights[]" placeholder="Weight"required/>';
        //Add a button to remove the commodity info 
        html += '<button type="button"  onclick="removeElement(this.parentNode)">Remove</button>'
        //Add a div to create space below each new commodity 
        html += '<div class="noborder"><br></div>';
        addElement("productDetails", "div", "commodity-" + commodityCount, html);
    }

    //function to add a new html element
    function addElement(parent, tag, id, html) {
        var setParent = document.getElementById(parent);
        var newElement = document.createElement(tag);
        newElement.setAttribute("id", id);
        newElement.innerHTML = html;
        setParent.appendChild(newElement);
    }

    //Remove element from its parent node
    function removeElement(element){
        element.parentNode.removeChild(element);
    }
    //Variable for storing commodities that are not updated in commodities
    var cUnchanged = "";
</script>


            </div>
            <div class="noborder">
                <button type="button" onclick="addCommodity()">Add Commodity</button>
                <button type="submit">Submit</button>
            </div>
        </form>
	
	<?php
        //Confirm that a product name was given in form submission
        if(isset($_POST["productname"])) {
            $name = strtolower($_POST["productname"]);
            //Attempt database connection (may move to top)
            try{
                $pdo = Connect::get()->connect();
                //Perform query to see if product already exists
	        $stm1 = $pdo->prepare("SELECT * FROM products WHERE LOWER(name) = :name");
                $stm1->execute([':name'=>$name]);
                $productReturn = $stm1->fetch(PDO::FETCH_ASSOC);               
                //free statement memory
                $stm1=null;
                //If the product doesn't exist, create a new one
                if(! $productReturn) {
                    //Create new product entry in products table
                    $productsInsert = $pdo->prepare('INSERT INTO products (name) VALUES (:name)');
                    $productsInsert->execute(['name'=>$name]);			
                    //free statement memory by setting to null
                    $productsInsert=null;
                    //Confirm that a commodity name was given in form submission
                    if(isset($_POST["commodities"])){
                        $i = 0;
                        //Insert commodity values into composition
                        foreach($_POST["commodities"] as $commodity){
                            $stm_com = $pdo->prepare('SELECT * FROM commodities WHERE name = :commodity');
                            $stm_com->execute([':commodity'=>$commodity]);
                            $commodityReturn = $stm_com->fetch(PDO::FETCH_ASSOC);		
                            $stm_con=null;
                            //see if commodity exists and if not insert
                            if(!($commodityReturn)){
                                $newcom = $pdo->prepare('INSERT INTO commodities (name, unit, price) VALUES (:commodity, :unit, :price)');
                                $newcom->execute([':commodity'=>$commodity, ':unit'=>$_POST["units"][$i], ':price'=>$_POST["prices"][$i]]);
                                $newcom=null;
                            }
                            else {
                                //Store unchanged commodites in string variable
                                echo "<script language='javascript'> cUnchanged+= ' ' + " . json_encode($commodity) . "; </script>";
                            }
                            $stm_comp = $pdo->prepare('INSERT INTO composition (unit_weight, productid, commodityid) VALUES (:weight, (select idpro from products where name=:name), (select idcomm from commodities where name=:commodity))');
                            $stm_comp->execute([':weight'=>$_POST["weights"][$i], ':name'=>$name, ':commodity'=>$commodity]);
                            $stm_comp=null;
                            $i++;
                        }
                    }
                    //Alert user to completed product addition
                    echo "<script type='text/javascript'>alert('product succesfully added');</script>";
                }
                else {
                    //Alert user to attempts to duplicate already existing product
                    echo "<script type='text/javascript'>alert('product already exists');</script>";
                } 
                //Close database connection
                $pdo=null;
                
            }catch(Exception $e) {
                echo 'Message: ' .$e->getMessage();
            }
        }
    ?>
    </div>
 
</body>
<script language="javascript">
    if(cUnchanged.length !== 0) {
        alert("The following commodity prices and weights were not changed as they already exist in the database:\n" + cUnchanged);
    }
</script>
</html>

