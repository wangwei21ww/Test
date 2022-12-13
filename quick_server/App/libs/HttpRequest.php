<?php

    function cURL_request_JSON($url, $data, $method='POST', $headers = [])
    {
        $curl = curl_init();
        $opt = [
            CURLOPT_PORT => parse_url($url, PHP_URL_PORT),
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_SSL_VERIFYPEER => false,
CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => is_array($data) ? json_encode($data) : $data,
            CURLOPT_HTTPHEADER => ["cache-control: no-cache", "content-type: application/json"],
        ];
        $opt[CURLOPT_HTTPHEADER] = array_merge($opt[CURLOPT_HTTPHEADER], $headers);

        curl_setopt_array($curl, $opt);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err != '') {
            throw new Exception('The request failed ' . $err, 912);
        }
        return $response;
    }

    // make a curl request
    function curlRequest($url, $request_data=[], $method='GET', $new_curl_options=[]) {
        if(strtoupper($method)=='GET') {
            $request_data = http_build_query($request_data);
            $url = $request_data!='' ? $url .'?'.$request_data : $url;
        }

        $curl_options = array( 
            CURLOPT_URL => $url, 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false, 
        );

        if(!in_array(strtoupper($method),array('GET','POST'))) {
            $curl_options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
            $curl_options[CURLOPT_POSTFIELDS] = $request_data;
        }

        if(strtoupper($method)=='POST') {
            $curl_options[CURLOPT_POST] = true;
            $curl_options[CURLOPT_POSTFIELDS] = $request_data;
        }

        $ch = curl_init();

        if(strpos(strtolower($url),'https')===0) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = false;
            $curl_options[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        // merge curl options
        foreach ($new_curl_options as $optionKey => $optionValue) {
            $curl_options[$optionKey] = $optionValue;
        }

        curl_setopt_array($ch,$curl_options);
        $original_data = $data = curl_exec($ch);
        $result['info'] = curl_getinfo($ch);

        curl_close($ch); 

        if(isset($curl_options[CURLOPT_HEADER]) and $curl_options[CURLOPT_HEADER]==true) {
            $header_size = isset($result['info']['header_size']) ? $result['info']['header_size'] : 0;
            $result['header'] = substr($original_data, 0, $header_size);
            $result['body'] = substr($original_data, $header_size);
            return $result;
        }
        return $data;
    }


    /**
      * Send a file to the target url with CURL
      * @param string $targetURL
      * @param string $file The upload file name with full path
      * @param array $extraData any extra data, if no extra data keep it an empty array
      * @return boolean
      */
    function curlSendFile($targetURL, $file, array $extraData = [])
    {
            $fileName = realpath($file);
            $post = array('extra' => $extraData, 'file_contents'=>'@'.$fileName);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $targetURL);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            $result=curl_exec ($ch);
            curl_close ($ch);
            return $result;
    }
