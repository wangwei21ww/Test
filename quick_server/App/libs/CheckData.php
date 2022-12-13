<?php
class CheckData
{
    static protected $params = [
        'language',
        'currency',
        'powerCoin',
        'kyc_firstname',
        'kyc_lastname',
        'kyc_gender',
        'kyc_zone',
        'kyc_identity_id',
        'kyc_identity_face',
        'kyc_identity_back',
        'kyc_identity_hold',
        'kyc_passport_id',
        'kyc_passport_cover ',
        'kyc_passport_hold ',
        'google_code_secret'
    ];

    static public function check($data)
    {
        if(isset($data['identity'])) {
            $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
            if (!preg_match($pattern,$data['identity'])){
                throw new Exception('The Email is invalid',900203);
            }
        }

        if (isset($data['password'])) {
            if(strlen($data['password']) < 6) {
                throw new Exception('Password is too short',900204);
            }
        }

        if(isset($data['params'])){
            foreach ($data['params'] as $key =>$value){
                if(!in_array($key,self::$params)){
                    throw new Exception($key.' is Invalid param',900204);
                }
            }
        }
        return true;
    }
}