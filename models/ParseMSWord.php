<?php

class ParseMSWord
{
    private $_ms_word_path;

    public function __construct($ms_word_path)
    {
        $this->_ms_word_path = $ms_word_path;
    }

    /*
     * WARNING!!!
     * only support Windows OS
     * 1. comment com.allow_dcom = true in php.ini
     * 2. add following sentences in php.ini
     *    [COM_DOT_NET]
     *    extension=php_com_dotnet.dll //make sure php_com_dotnet.dll exist in ext folder
     * 3. restart web server
     * */
    public function parseDocByCom()
    {
        if (PHP_OS === 'Linux')
        {
            echo 'This method only support Windows OS.';
            exit;
        }

        $word = new COM("word.application") or die ("Could not initialise MS Word object.");
        $word->Documents->Open(realpath($this->_ms_word_path));

        // Extract content.
        $content = (string) $word->ActiveDocument->Content;

        $word->ActiveDocument->Close(false);

        //closing word
        $word->Quit();
        //free the object
        $word = null;

        return $content;
    }

    /*
     * Failed
     * */
    public function convertDoc2DocxByCom()
    {
        if (PHP_OS === 'Linux')
        {
            echo 'This method only support Windows OS.';
            exit;
        }

        $word = new COM("word.application") or die ("Could not initialise MS Word object.");
        $word->Visible = 0;
        $word->DisplayAlerts = 0;
        $word->Documents->Open(realpath($this->_ms_word_path));
        $word->ActiveDocument->SaveAs('D:/newdocument.docx');
        $word->ActiveDocument->Close(false);
        //closing word
        $word->Quit();
        //free the object
        $word = null;
    }

    /*
     * Failed
     * */
    public function parseDocVersionOne()
    {
        $fileHandle = fopen($this->_ms_word_path, "r");
        $line = @fread($fileHandle, filesize($this->_ms_word_path));
        $lines = explode(chr(0x0D), $line);
        $output = "";
        foreach($lines as $line)
        {
            $pos = strpos($line, chr(0x00));
            if (($pos !== FALSE) || (strlen($line) == 0))
            {

            }
            else
            {
                $output .= $line . " ";
            }
        }
        $output = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/", "", $output);

        return $output;
    }

    /*
     * Failed
     * */
    public function parseDocVersionTwo()
    {
        $fileHandle = fopen($this->_ms_word_path, "r");
        $word_text = @fread($fileHandle, filesize($this->_ms_word_path));print_r($word_text);exit;
        $line = "";
        $tam = filesize($this->_ms_word_path);
        $nulos = 0;
        $caracteres = 0;
        for($i=1536; $i<$tam; $i++)
        {
            $line .= $word_text[$i];

            if( $word_text[$i] == 0)
            {
                $nulos++;
            }
            else
            {
                $nulos=0;
                $caracteres++;
            }

            if( $nulos>1996)
            {
                break;
            }
        }

        //echo $caracteres;

        $lines = explode(chr(0x0D),$line);
        //$outtext = "<pre>";

        $outtext = "";
        foreach($lines as $thisline)
        {
            $tam = strlen($thisline);
            if( !$tam )
            {
                continue;
            }

            $new_line = "";
            for($i=0; $i<$tam; $i++)
            {
                $onechar = $thisline[$i];
                if( $onechar > chr(240) )
                {
                    continue;
                }

                if( $onechar >= chr(0x20) )
                {
                    $caracteres++;
                    $new_line .= $onechar;
                }

                if( $onechar == chr(0x14) )
                {
                    $new_line .= "</a>";
                }

                if( $onechar == chr(0x07) )
                {
                    $new_line .= "\t";
                    if( isset($thisline[$i+1]) )
                    {
                        if( $thisline[$i+1] == chr(0x07) )
                        {
                            $new_line .= "\n";
                        }
                    }
                }
            }
            //troca por hiperlink
            $new_line = str_replace("HYPERLINK" ,"<a href=",$new_line);
            $new_line = str_replace("\o" ,">",$new_line);
            $new_line .= "\n";

            //link de imagens
            $new_line = str_replace("INCLUDEPICTURE" ,"<br><img src=",$new_line);
            $new_line = str_replace("\*" ,"><br>",$new_line);
            $new_line = str_replace("MERGEFORMATINET" ,"",$new_line);


            $outtext .= nl2br($new_line);
        }

        return $outtext;
    }

    /*
     * Failed
     * reference url: http://stackoverflow.com/questions/7358637/reading-doc-file-in-php
     * */
    public function parseDocVersionThree()
    {
        if(($fh = fopen($this->_ms_word_path, 'r')) !== false )
        {
            $headers = fread($fh, 0xA00);

            // 1 = (ord(n)*1) ; Document has from 0 to 255 characters
            $n1 = ( ord($headers[0x21C]) - 1 );

            // 1 = ((ord(n)-8)*256) ; Document has from 256 to 63743 characters
            $n2 = ( ( ord($headers[0x21D]) - 8 ) * 256 );

            // 1 = ((ord(n)*256)*256) ; Document has from 63744 to 16775423 characters
            $n3 = ( ( ord($headers[0x21E]) * 256 ) * 256 );

            // 1 = (((ord(n)*256)*256)*256) ; Document has from 16775424 to 4294965504 characters
            $n4 = ( ( ( ord($headers[0x21F]) * 256 ) * 256 ) * 256 );

            // Total length of text in the document
            $textLength = ($n1 + $n2 + $n3 + $n4);

            $extracted_plaintext = fread($fh, $textLength);

            // simple print character stream without new lines
            //echo $extracted_plaintext;

            // if you want to see your paragraphs in a new line, do this
            return nl2br($extracted_plaintext);
            // need more spacing after each paragraph use another nl2br
        }

        return '';
    }
    /*
     * reference url: http://www.blogs.zeenor.com/it/read-ms-word-docx-ms-word-2007-file-document-using-php.html
     * */
    public function parseDocx()
    {
        if (substr($this->_ms_word_path, -4) != 'docx')
        {
            echo 'Only support docx file';
            return false;
        }

        $zip = zip_open($this->_ms_word_path);

        if (!$zip || is_numeric($zip))
        {
            return false;
        }

        $ms_docx_content = '';
        while ($zip_entry = zip_read($zip))
        {
            if (zip_entry_open($zip, $zip_entry) == FALSE)
            {
                continue;
            }
            if (zip_entry_name($zip_entry) != "word/document.xml")
            {
                continue;
            }

            $ms_docx_content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
            zip_entry_close($zip_entry);
        }

        zip_close($zip);

        $ms_docx_content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $ms_docx_content);
        $ms_docx_content = str_replace('</w:r></w:p>', "\r\n", $ms_docx_content);
        $striped_content = strip_tags($ms_docx_content);

        return $striped_content;
    }
} 