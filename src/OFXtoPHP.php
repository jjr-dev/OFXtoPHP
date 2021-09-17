<?php
  class OFXtoPHP
  {
      private $file;
      private $configs;
      private $ofx;

      function __construct()
      {
          $this->configs = (object) array(
              'closeTags' => true,
              'returnObject' => true
          );
      }

      function applyMask($mask, $string)
      {
          $mask = str_replace('#', '%s', $mask);
          return  vsprintf($mask, str_split($string));
      }

      function setFile($path)
      {
          $this->file = file_get_contents($path);
      }

      function setConfigs($configs)
      {
          if (isset($configs['closeTags'])) {
              $this->configs->closeTags = $configs['closeTags'];
          }

          if (isset($configs['returnObject'])) {
              $this->configs->returnObject = $configs['returnObject'];
          }
      }

      function convertOfxToArray()
      {
          $start_ofx  = strstr($this->file, "<OFX>", true);
          $ofx_str    = str_replace($start_ofx, '', $this->file);

          if ($this->configs->closeTags) {
              $ofx_exp    = explode("\n", $ofx_str);

              $ofx_full   = '';

              foreach ($ofx_exp as $line) {
                  if (strpos($line, '</') === false) {

                      $endTag = strpos($line, '>');

                      $lineTag = substr($line, 1, $endTag - 1);
                      $content = substr($line, $endTag + 1);

                      if (strlen($content) > 0) {
                          $ofx_full .= "<{$lineTag}>$content</{$lineTag}>\n";
                      } else {
                          $ofx_full .=  "<{$lineTag}>\n";
                      }
                  } else {
                      $ofx_full .=  "$line\n";
                  }
              }

              $ofx_full   = trim(str_replace('<>', '', $ofx_full));
          } else {
              $ofx_full = $ofx_str;
          }

          $ofx_xml    = simplexml_load_string($ofx_full, "SimpleXMLElement", LIBXML_NOCDATA);
          $ofx_json   = json_encode($ofx_xml);
          $this->ofx  = json_decode($ofx_json, true);

          return $this->ofx;
      }

      function getBank()
      {
          $return = array(
              'name'  => $this->ofx['SIGNONMSGSRSV1']['SONRS']['FI']['ORG'],
              'id'    => $this->ofx['SIGNONMSGSRSV1']['SONRS']['FI']['FID']
          );

          return ($this->configs->returnObject) ? (object) $return : (array) $return;
      }

      function getDates()
      {
          $mask = '####-##-## ##:##:##';

          $return = array(
              'generate'  =>  $this->applyMask($mask, $this->ofx['SIGNONMSGSRSV1']['SONRS']['DTSERVER']),
              'start'     =>  $this->applyMask($mask, $this->ofx['BANKMSGSRSV1']['STMTTRNRS']['STMTRS']['BANKTRANLIST']['DTSTART']),
              'end'       =>  $this->applyMask($mask, $this->ofx['BANKMSGSRSV1']['STMTTRNRS']['STMTRS']['BANKTRANLIST']['DTEND'])
          );

          return ($this->configs->returnObject) ? (object) $return : (array) $return;
      }

      function getAccount()
      {
          $return = array(
              'account'  =>  $this->ofx['BANKMSGSRSV1']['STMTTRNRS']['STMTRS']['BANKACCTFROM']['ACCTID'],
              'type'     =>  $this->ofx['BANKMSGSRSV1']['STMTTRNRS']['STMTRS']['BANKACCTFROM']['ACCTTYPE']

          );

          return ($this->configs->returnObject) ? (object) $return : (array) $return;
      }

      function getTransactions()
      {
          $transactions = $this->ofx['BANKMSGSRSV1']['STMTTRNRS']['STMTRS']['BANKTRANLIST']['STMTTRN'];

          $object = array();
          foreach($transactions as $transaction) {
              $transactionOrganized = array(
                  'id'            => $transaction['FITID'],
                  'type'          => $transaction['TRNTYPE'],
                  'description'   => $transaction['MEMO'],
                  'amount'        => $transaction['TRNAMT'] * -1,
                  'date'          => $this->applyMask('####-##-## ##:##:##', $transaction['DTPOSTED']),
                  'positive'      => ($transaction['TRNAMT'] > 0) ? true : false
              );

              $transactionOrganized = ($this->configs->returnObject) ? (object) $transactionOrganized : (array) $transactionOrganized;

              $object[$transaction['FITID']] = $transactionOrganized;
          }

          return ($this->configs->returnObject) ? (object) $object : (array) $object;
      }
  }
