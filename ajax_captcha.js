//Gets the browser specific XmlHttpRequest Object
function getXmlHttpRequestObject() {
 if (windo w.XMLHttpRequest) {
    return new XMLHttpRequest(); //Mozilla, Safari ...
 } else if (windo w.ActiveXObject) {
    return new ActiveXObject("Microsoft.XMLHTTP"); //IE
 } else {
    //Display our error message
    ale rt("Your browser doesn't support the XmlHttpRequest object.");
 }
}
//Our XmlHttpRequest object
var receiveReq = getXmlHttpRequestObject();
//Initiate the AJAX request
function makeRequest(url, param) {
//If our readystate is either not started or finished, initiate a new request
 if (receiveReq.readyState == 4 || receiveReq.readyState == 0) {
   //Set up the connection to captcha_test.html. True sets the request to asyncronous(default)
   receiveReq.open("POST", url, true);
   //Set the function that will be called when the XmlHttpRequest objects state changes
   receiveReq.onreadystatechange = updatePage;

   //Add HTTP headers to the request
   receiveReq.setRequestHeader("Conten t-type", "application/x-www-form-urlencoded");
   receiveReq.setRequestHeader("Conten t-length", param.length);
   receiveReq.setRequestHeader("Connection", "close");

   //Make the request
   receiveReq.send(param);
 }
}
//Called every time our XmlHttpRequest objects state changes
function updatePage() {
 //Check if our response is ready
 if (receiveReq.readyState == 4) {
   //Set the conten t of the DIV element with the response text
   documen t.getElementById('result').innerHTML = receiveReq.responseText;
   //Get a reference to CAPTCHA image
   img = documen t.getElementById('imgCaptcha');
   //Change the image
   img.src = 'captcha.php?' + Math.random();
 }
}

//Called every time when form is perfomed
function getParam(theForm) {
 //Set the URL
 var url = 'captcha.php';
 //Set up the parameters of our AJAX call
 var postStr = theForm.txtCaptcha.nam e + "=" + encodeURIComponent( theForm.txtCaptcha.value );
 //Call the function that initiate the AJAX request
 makeRequest(url, postStr);
}  