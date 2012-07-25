<?php # saletags.php - Generate sales tags from sales batches.
// if (!isset($_POST['tags']) || !isset($_POST['batchID'])) { // Accessed in error.
//     $page_title = 'Fannie - Sales Batch Module';
//     $header = 'Sales Tag Creator';
//     include ('../src/header.php');
//     echo '<p><font color="red">This page has been accessed in error.</font></p>';
//     include ('../src/footer.php');
//     exit;
// } else { // Valid page hit, grab info, print tags.
    require_once ('../define.conf');
	include ('../src/functions.php');
    $batchID = (!$_POST['batchID']) ? escape_data($_GET['batchID']) : escape_data($_POST['batchID']);
    
    $query = "SELECT DATE_FORMAT(endDate, '%c/%d/%y'),batchType FROM batches WHERE batchID=$batchID";
    $result = mysql_query($query) OR die(mysql_error() . "<br />" . $query);
    
	$typeR = mysql_query("SELECT batchType FROM batches WHERE batchID = $batchID");
	
    if (mysql_num_rows($result) == 1) { // Success, set variables.
        $endDate = mysql_result($result, 0);
		$btype = mysql_fetch_row($typeR);
		$batchType = $btype[0];
    	switch ($batchType) {
			case 1:
				$bg_file = "Sale1up2.jpg";
				break;
			case 2:
				$bg_file = "OwnerMemberSale1up.jpg";
				break;
			case 5:
				$bg_file = "BlowOut1up.jpg";
				break;
			default:
				$bg_file = "";
				
		}

	} else { // Problem!
        $page_title = 'Fannie - Sales Batch Module';
        $header = 'Sales Tag Creator';
        include ('../src/header.php');
        echo '<p><font color="red">That sales batch could not be found.</font></p>';
        include ('../src/footer.php');
        exit;
    }        
	
    $query = "SELECT pd.brand AS brand, SUBSTR(p.description,1,20) AS description, pd.description AS ldescription, p.normal_price AS nprice, b.salePrice AS sprice
                FROM product_details AS pd
                INNER JOIN batchList AS b ON (pd.upc = b.upc)
                INNER JOIN " . PRODUCTS_TBL . " AS p ON (p.upc = b.upc)
                WHERE b.batchID=$batchID";
    $result = mysql_query($query) OR die(mysql_error() . "<br />" . $query);
// }
    
require('../src/fpdf/fpdf.php');
define('FPDF_FONTPATH','font/');
    
  /**
   * begin to create PDF file using fpdf functions
   **/
    $h = 80;
    $w = 60;
    $top = 15;
    $left = 6;
    $x = 15;
    $y = 15;
    $endDate = 'prices good thru ' . $endDate;
  
  $pdf=new FPDF('P', 'mm', 'Letter');
  $pdf->SetMargins($left ,$top);
  $pdf->SetAutoPageBreak('off',0);
  $pdf->AddPage('P');
  $pdf->SetFont('Arial','',10);
  
  /**
   * set up location variable starts
   **/
   
  $brandTop = 33;
  $productTop = 40;
  $priceLeft = $x + 12.7;
  $spriceTop = 53;
  $npriceTop = 64;
  $endDateTop = 69;
  $tagCount = 0;
  $down = 80;
  $LeftShift = 63;
  $lineStartX = $x + 10;
  $lineStopX = $x + $w - 10;
  $lineStartY = 38;
  $lineStopY = 38;

  
  /**
   * increment through items in query
   **/
   
  while ($row = mysql_fetch_array($result)){
     /**
      * check to see if we have made 6 tags.
      * if we have start a new page....
      */
      
     if($tagCount == 9){
        $pdf->AddPage('P');
        $y = 15;
        $x = 15;
        $brandTop = 35;
        $productTop = 42;
        $priceLeft = $x + 12.7;
        $spriceTop = 55;
        $npriceTop = 65;
        $endDateTop = 70;
        $tagCount = 0;
        $lineStartX = $x + 10;
        $lineStopX = $x + $w - 10;
        $lineStartY = 38;
        $lineStopY = 38;

     }
  
     /** 
      * check to see if we have reached the right most label
      * if we have reset all left hands back to initial values
      */
     if($x > 165){
        $y = $y + $down;
        $x = 15;
        $brandTop = $brandTop + $down;
        $lineStartX = $x + 10;
        $lineStopX = $x + $w - 10;
        $lineStartY = $lineStartY + $down;
        $lineStopY = $lineStopY + $down;
        $priceLeft = $x + 12.7;
        $spriceTop = $spriceTop + $down;
        $npriceTop = $npriceTop + $down;
        $productTop = $productTop + $down;
        $endDateTop = $endDateTop + $down;
        
     }
  
  /**
   * instantiate variables for printing on barcode from 
   * $testQ query result set
   */
     $product = $row['ldescription'];

	 if (strlen($product) > 20) {
		$product_fontsize = 11;
		$product_len = 30;
  	 } else {
		$product_fontsize = 13;
		$product_len = 20;
 	 }
	 $product = substr($product, 0, $product_len);
	
     $brand = ucwords(strtolower($row['brand']));
     $nprice = '$' . number_format($row['nprice'],2);
     $sprice = '$' . number_format($row['sprice'],2);
  
  /**
   * begin creating tag
   */
  $pdf->SetLineWidth(0);
  $pdf->Rect($x, $y, $w, $h-4);
  // $pdf->Image('Sale1up2.jpg', $x, $y, $w, $h-4);
  $pdf->Image($bg_file, $x, $y, $w, $h-4);
  $pdf->SetFont('Arial','',11);
  $pdf->SetXY($x, $brandTop);
  $pdf->Cell($w,8,$brand,0,0,'C');
  // $pdf->SetLineWidth(.4);
  // $pdf->Line($lineStartX, $lineStartY, $lineStopX, $lineStopY);
  $pdf->SetFont('Arial','B',42);
  $pdf->SetXY($priceLeft,$spriceTop);
  // $pdf->Cell($w-25.4,4,'Sale Price',0,0,'L');
  // $pdf->SetXY($priceLeft,$spriceTop);
  $pdf->Cell($w-25.4,4,$sprice,0,0,'C');
  $pdf->SetFont('Arial','',14);
  $pdf->SetXY($priceLeft,$npriceTop);
  $pdf->Cell($w-25.4,4,'Regular Price  ' . $nprice,0,0,'C');
  // $pdf->SetXY($priceLeft,$npriceTop);
  // $pdf->Cell($w-25.4,4,$nprice,0,0,'R');
  $pdf->SetFont('Arial','B',$product_fontsize);
  $pdf->SetXY($x, $productTop);
  $pdf->Cell($w,6,$product,0,0,'C');
  $pdf->SetFont('Arial','I',10);
  $pdf->SetXY($x, $endDateTop);
  $pdf->Cell($w,3,$endDate,0,0,'C');

  /**
   * increment label parameters for next label
   */
    $x = $x + $LeftShift;
    $priceLeft = $priceLeft + $LeftShift;
    $lineStartX = $lineStartX + $LeftShift;
    $lineStopX = $lineStopX + $LeftShift;
    $tagCount++;
  }
  
  /**
   * write to PDF
   */
  $pdf->Output();


?>