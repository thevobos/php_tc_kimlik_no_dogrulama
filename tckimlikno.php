<?php
/**
 * Created by PhpStorm.
 * User: cengizakcan
 * Date: 17.12.2020
 * Time: 21:13
 */

namespace App\Main\Model;


class TCKimlikNoSinifi {

    var $KimlikNo;
    var $Ad;
    var $Soyad;
    var $DogumGun;
    var $DogumAy;
    var $DogumYil;

    public function BuyukHarfCevir($text)
    {
        $text       = trim($text);
        $search     = array('ç','ğ','ı','ö','ş','ü','i');
        $replace    = array('Ç','Ğ','I','Ö','Ş','Ü','İ');
        $new_text   = str_replace($search,$replace,$text);

        return mb_strtoupper($new_text);

    }


    public function TCKimlikNoDogrulaAlgoritma($TCKimlikNo)
    {
        if ( strlen($TCKimlikNo) == 11 )
        {
            $basamak = str_split($TCKimlikNo);
            $basamak1 = $basamak[0];
            $basamak2 = $basamak[1];
            $basamak3 = $basamak[2];
            $basamak4 = $basamak[3];
            $basamak5 = $basamak[4];
            $basamak6 = $basamak[5];
            $basamak7 = $basamak[6];
            $basamak8 = $basamak[7];
            $basamak9 = $basamak[8];
            $basamak10 = $basamak[9];
            $basamak11 = $basamak[10];

            $basamak10_test=fmod( ( $basamak1 + $basamak3 + $basamak5 + $basamak7 + $basamak9 ) * 7  - ( $basamak2 + $basamak4 + $basamak6 + $basamak8 )     ,10) ;
            $basamak11_test = fmod( $basamak1 + $basamak2 + $basamak3 + $basamak4 + $basamak5 + $basamak6 + $basamak7 + $basamak8 + $basamak9 + $basamak10     ,10);
        }

        if ( strlen($TCKimlikNo) != 11 ) {
            return false;
        }
        elseif ($basamak1 == 0) {
            return false;
        }
        elseif (!is_numeric($basamak1) or !is_numeric($basamak2) or !is_numeric($basamak3) or  !is_numeric($basamak4) or !is_numeric($basamak5) or !is_numeric($basamak6) or !is_numeric($basamak7) or !is_numeric($basamak8) or !is_numeric($basamak9) or  !is_numeric($basamak10) or !is_numeric($basamak11) ) {
            return false;
        }
        elseif($basamak10_test != $basamak10) {
            return false;
        }
        elseif($basamak11_test != $basamak11 ) {
            return false;
        }
        else {
            return true;
        }
    }




    public function TCKimlikNoDogrulaCurl($TCKimlikNo,$Ad,$Soyad,$DogumYil)
    {
        $gonder = '<?xml version="1.0" encoding="utf-8"?>
			<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
			<soap:Body>
			<TCKimlikNoDogrula xmlns="http://tckimlik.nvi.gov.tr/WS">
			<TCKimlikNo>'.$TCKimlikNo.'</TCKimlikNo>
			<Ad>'.$this->BuyukHarfCevir($Ad).'</Ad>
			<Soyad>'.$this->BuyukHarfCevir($Soyad).'</Soyad>
			<DogumYili>'.$DogumYil.'</DogumYili>
			</TCKimlikNoDogrula>
			</soap:Body>
			</soap:Envelope>';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx" );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $gonder);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'POST /Service/KPSPublic.asmx HTTP/1.1',
            'Host: tckimlik.nvi.gov.tr',
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: '.strlen($gonder)
        ));

        $gelen = curl_exec($ch);
        curl_close($ch);
        $gelensonuc=strip_tags($gelen);

        if ($gelensonuc) {
            return true;
        }
        else {
            return false;
        }
    }




    public function TCKimlikNoDogrulaSoap($TCKimlikNo,$Ad,$Soyad,$DogumYil)
    {

        $https = stream_context_create(
            [
                'ssl' =>
                    [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
            ]);



        $client = new SoapClient('https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx?WSDL',
            array( 'trace' => 1,
                'soapaction' => 'http://tckimlik.nvi.gov.tr/WS/TCKimlikNoDogrula',
                'encoding' => 'UTF-8',
                'user_agent' => '',
                'keep_alive' => false,
                'cache_wsdl' => WSDL_CACHE_NONE,    //wsdl önbellek kapalı
                'stream_context' => $https  // Ssl certificate
            ) );

        $gonder = new SoapVar('<TCKimlikNoDogrula xmlns="http://tckimlik.nvi.gov.tr/WS"><TCKimlikNo>'.$TCKimlikNo.'</TCKimlikNo><Ad>'.$this->BuyukHarfCevir($Ad).'</Ad><Soyad>'.$this->BuyukHarfCevir($Soyad).'</Soyad><DogumYili>'.$DogumYil.'</DogumYili></TCKimlikNoDogrula>', XSD_ANYXML);

        $gelensonuc = $client->TCKimlikNoDogrula($gonder)->TCKimlikNoDogrulaResult;

        if ($gelensonuc) {
            return true;
        }
        else {
            return false;
        }
    }




    public function YabanciKimlikNoDogrulaCurl($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil)
    {
        $gonder = '<?xml version="1.0" encoding="utf-8"?>
			<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
			<soap:Body>
			<YabanciKimlikNoDogrula xmlns="http://tckimlik.nvi.gov.tr/WS">
			<KimlikNo>'.$TCKimlikNo.'</KimlikNo>
			<Ad>'.$this->BuyukHarfCevir($Ad).'</Ad>
			<Soyad>'.$this->BuyukHarfCevir($Soyad).'</Soyad>
			<DogumGun>'.$DogumGun.'</DogumGun>
			<DogumAy>'.$DogumAy.'</DogumAy>
			<DogumYil>'.$DogumYil.'</DogumYil>
			</YabanciKimlikNoDogrula>
			</soap:Body>
			</soap:Envelope>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://tckimlik.nvi.gov.tr/Service/KPSPublicYabanciDogrula.asmx" );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $gonder);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'POST /Service/KPSPublicYabanciDogrula.asmx HTTP/1.1',
            'Host: tckimlik.nvi.gov.tr',
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: '.strlen($gonder)
        ));
        $gelen = curl_exec($ch);
        curl_close($ch);
        $gelensonuc=strip_tags($gelen);

        if ($gelensonuc) {
            return true;
        }
        else {
            return false;
        }
    }


    public function YabancıKimlikNoDogrulaSoap ($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil)
    {

        $https = stream_context_create(
            [
                'ssl' =>
                    [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
            ]);



        $client = new SoapClient('https://tckimlik.nvi.gov.tr/Service/KPSPublicYabanciDogrula.asmx?WSDL',
            array( 'trace' => 1,
                'soapaction' => 'http://tckimlik.nvi.gov.tr/WS/YabanciKimlikNoDogrula',
                'encoding' => 'UTF-8',
                'user_agent' => '',
                'keep_alive' => false,
                'cache_wsdl' => WSDL_CACHE_NONE,    //wsdl önbellek kapalı
                'stream_context' => $https  // Ssl certificate
            ) );

        $gonder = new SoapVar('<YabanciKimlikNoDogrula xmlns="http://tckimlik.nvi.gov.tr/WS"> <KimlikNo>'.$TCKimlikNo.'</KimlikNo><Ad>'.$this->BuyukHarfCevir($Ad).'</Ad><Soyad>'.$this->BuyukHarfCevir($Soyad).'</Soyad><DogumGun>'.$DogumGun.'</DogumGun><DogumAy>'.$DogumAy.'</DogumAy><DogumYil>'.$DogumYil.'</DogumYil> </YabanciKimlikNoDogrula>',XSD_ANYXML);

        $gelensonuc = $client->YabanciKimlikNoDogrula($gonder)->YabanciKimlikNoDogrulaResult;

        if ($gelensonuc) {
            return true;
        } else {
            return false;
        }

    }


    public function YabanciKimlikMi($TCKimlikNo)
    {
        if ( strlen($TCKimlikNo) == 11 )
        {
            $basamak = str_split($TCKimlikNo);
            $basamak1 = $basamak[0];
            $basamak2 = $basamak[1];

            if ($basamak1=="9" and $basamak2=="9")
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        if ( strlen($TCKimlikNo) != 11 )
        {
            return false;
        }
    }



    public function TCKimlikNoDogrula($TCKimlikNo,$Ad,$Soyad,$DogumYil){

        $algoritma_sonuc = $this->TCKimlikNoDogrulaAlgoritma($TCKimlikNo);

        if ($algoritma_sonuc){

            if( function_exists('curl_version') == true){
                return $this->TCKimlikNoDogrulaCurl($TCKimlikNo,$Ad,$Soyad,$DogumYil);
            }elseif ( class_exists('SOAPClient') == true){
                return $this->TCKimlikNoDogrulaSoap($TCKimlikNo,$Ad,$Soyad,$DogumYil);
            }elseif( function_exists('curl_version') == false and class_exists('SOAPClient') == false){
                return true;
            }

        }

        if (!$algoritma_sonuc){
            return false;
        }

    }



    public function  YabanciKimlikNoDogrula($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil)
    {
        $algoritma_sonuc = $this->TCKimlikNoDogrulaAlgoritma($TCKimlikNo);

        if ($algoritma_sonuc){

            if( function_exists('curl_version') == true){
                return $this->YabanciKimlikNoDogrulaCurl($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil);
            }
            elseif ( class_exists('SOAPClient') == true){
                return $this->YabancıKimlikNoDogrulaSoap($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil);
            }
            elseif( function_exists('curl_version') == false and class_exists('SOAPClient') == false){
                return true;
            }

        }

        if (!$algoritma_sonuc){
            return false;
        }
    }



    public function KimlikNoDogrula($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil)
    {

        $yabancikimlikturu=$this->YabanciKimlikMi($TCKimlikNo);

        if( $yabancikimlikturu) {
            return $this->YabanciKimlikNoDogrula($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil);
        }
        elseif( $yabancikimlikturu) {
            return $this->TCKimlikNoDogrula($TCKimlikNo,$Ad,$Soyad,$DogumYil);
        }

    }

    public function NufusCuzdaniDogrulaCurl($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$CuzdanSeri,$CuzdanNo)
    {
        $gonder = '<?xml version="1.0" encoding="utf-8"?>
						<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
						<soap:Body>
						<KisiVeCuzdanDogrula xmlns="http://tckimlik.nvi.gov.tr/WS">
						<TCKimlikNo>'.$TCKimlikNo.'</TCKimlikNo>
						<Ad>'.$this->BuyukHarfCevir($Ad).'</Ad>
						<Soyad>'.$this->BuyukHarfCevir($Soyad).'</Soyad>
						<DogumGun>'.$DogumGun.'</DogumGun>
						<DogumAy>'.$DogumAy.'</DogumAy>
						<DogumYil>'.$DogumYil.'</DogumYil>
						<CuzdanSeri>'.$this->BuyukHarfCevir($CuzdanSeri).'</CuzdanSeri>
						<CuzdanNo>'.$CuzdanNo.'</CuzdanNo>
						</KisiVeCuzdanDogrula>
						</soap:Body>
						</soap:Envelope>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://tckimlik.nvi.gov.tr/Service/KPSPublicV2.asmx" );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $gonder);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'POST /Service/KPSPublicV2.asmx HTTP/1.1',
            'Host: tckimlik.nvi.gov.tr',
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: '.strlen($gonder)
        ));
        $gelen = curl_exec($ch);
        curl_close($ch);
        $sonuc=strip_tags($gelen);

        return $sonuc;
    }




    public function NufusCuzdaniDogrulaSoap($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$CuzdanSeri,$CuzdanNo)
    {
        $https = stream_context_create(
            [
                'ssl' =>
                    [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
            ]);



        $client = new SoapClient('https://tckimlik.nvi.gov.tr/Service/KPSPublicV2.asmx?WSDL',
            array( 'trace' => 1,
                'soapaction' => 'http://tckimlik.nvi.gov.tr/WS/KisiVeCuzdanDogrula',
                'encoding' => 'UTF-8',
                'user_agent' => '',
                'keep_alive' => false,
                'cache_wsdl' => WSDL_CACHE_NONE,    //wsdl önbellek kapalı
                'stream_context' => $https  // Ssl certificate
            ) );

        $gonder = new SoapVar('<KisiVeCuzdanDogrula xmlns="http://tckimlik.nvi.gov.tr/WS"> 
			<TCKimlikNo>'.$TCKimlikNo.'</TCKimlikNo>
			<Ad>'.$this->BuyukHarfCevir($Ad).'</Ad>
			<Soyad>'.$this->BuyukHarfCevir($Soyad).'</Soyad>
			<DogumGun>'.$DogumGun.'</DogumGun>
			<DogumAy>'.$DogumAy.'</DogumAy>
			<DogumYil>'.$DogumYil.'</DogumYil>
			<CuzdanSeri>'.$this->BuyukHarfCevir($CuzdanSeri).'</CuzdanSeri>
			<CuzdanNo>'.$CuzdanNo.'</CuzdanNo>
			</KisiVeCuzdanDogrula>', XSD_ANYXML);

        $sonuc = $client->KisiVeCuzdanDogrula($gonder)->KisiVeCuzdanDogrulaResult;

        return $sonuc;
    }





    public function KimlikKartiDogrulaCurl($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$TCKKSeriNo)
    {
        $gonder = '<?xml version="1.0" encoding="utf-8"?>
						<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
						<soap:Body>
						<KisiVeCuzdanDogrula xmlns="http://tckimlik.nvi.gov.tr/WS">
						<TCKimlikNo>'.$TCKimlikNo.'</TCKimlikNo>
						<Ad>'.$this->BuyukHarfCevir($Ad).'</Ad>
						<Soyad>'.$this->BuyukHarfCevir($Soyad).'</Soyad>
						<DogumGun>'.$DogumGun.'</DogumGun>
						<DogumAy>'.$DogumAy.'</DogumAy>
						<DogumYil>'.$DogumYil.'</DogumYil>
						<TCKKSeriNo>'.$this->BuyukHarfCevir($TCKKSeriNo).'</TCKKSeriNo>
						</KisiVeCuzdanDogrula>
						</soap:Body>
						</soap:Envelope>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://tckimlik.nvi.gov.tr/Service/KPSPublicV2.asmx" );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $gonder);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'POST /Service/KPSPublicV2.asmx HTTP/1.1',
            'Host: tckimlik.nvi.gov.tr',
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: '.strlen($gonder)
        ));
        $gelen = curl_exec($ch);
        curl_close($ch);
        $sonuc=strip_tags($gelen);

        return $sonuc;
    }

    public function KimlikKartiDogrulaSoap($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$TCKKSeriNo)
    {
        $https = stream_context_create(
            [
                'ssl' =>
                    [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
            ]);



        $client = new SoapClient('https://tckimlik.nvi.gov.tr/Service/KPSPublicV2.asmx?WSDL',
            array( 'trace' => 1,
                'soapaction' => 'http://tckimlik.nvi.gov.tr/WS/KisiVeCuzdanDogrula',
                'encoding' => 'UTF-8',
                'user_agent' => '',
                'keep_alive' => false,
                'cache_wsdl' => WSDL_CACHE_NONE,    //wsdl önbellek kapalı
                'stream_context' => $https  // Ssl certificate
            ) );


        $gonder = new SoapVar('<KisiVeCuzdanDogrula xmlns="http://tckimlik.nvi.gov.tr/WS"> 
			<TCKimlikNo>'.$TCKimlikNo.'</TCKimlikNo>
			<Ad>'.$this->BuyukHarfCevir($Ad).'</Ad>
			<Soyad>'.$this->BuyukHarfCevir($Soyad).'</Soyad>
			<DogumGun>'.$DogumGun.'</DogumGun>
			<DogumAy>'.$DogumAy.'</DogumAy>
			<DogumYil>'.$DogumYil.'</DogumYil>
			<TCKKSeriNo>'.$this->BuyukHarfCevir($TCKKSeriNo).'</TCKKSeriNo>
			</KisiVeCuzdanDogrula>', XSD_ANYXML);

        $sonuc = $client->KisiVeCuzdanDogrula($gonder)->KisiVeCuzdanDogrulaResult;

        return $sonuc;
    }




    public function NufusCuzdaniDogrula($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$CuzdanSeri,$CuzdanNo)
    {
        $algoritma_sonuc = $this->TCKimlikNoDogrulaAlgoritma($TCKimlikNo);

        if ($algoritma_sonuc) {

            if( function_exists('curl_version') == true){
                return $this->NufusCuzdaniDogrulaCurl($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$CuzdanSeri,$CuzdanNo);
            }
            elseif ( class_exists('SOAPClient') == true) {
                return $this->NufusCuzdaniDogrulaSoap($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$CuzdanSeri,$CuzdanNo);
            }
            elseif( function_exists('curl_version') == false and class_exists('SOAPClient') == false) //curl ve soap çalışmıyorsa sadece algoritma kontrolü yap
            {
                return true;
            }

        }

        if (!$algoritma_sonuc)
        {
            return false;
        }
    }


    public function KimlikKartiDogrula($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$TCKKSeriNo)
    {
        $algoritma_sonuc = $this->TCKimlikNoDogrulaAlgoritma($TCKimlikNo);

        if ($algoritma_sonuc) {

            if( function_exists('curl_version') == true)
            {
                return $this->KimlikKartiDogrulaCurl($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$TCKKSeriNo);
            }
            elseif ( class_exists('SOAPClient') == true)
            {
                return $this->KimlikKartiDogrulaSoap($TCKimlikNo,$Ad,$Soyad,$DogumGun,$DogumAy,$DogumYil,$TCKKSeriNo);
            }
            elseif( function_exists('curl_version') == false and class_exists('SOAPClient') == false) //curl ve soap çalışmıyorsa sadece algoritma kontrolü yap
            {
                return true;
            }

        }

        if (!$algoritma_sonuc){
            return false;
        }
    }


}
