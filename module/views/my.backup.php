<?php
    use CTable;
    use CCol;
    use CColHeader;
    use CLink;
    use CHtmlPage;
    use CDiv;
    use CButton;
    use CTag;
   
    // Set autorefresh time interval
    $autorefresh = 30;

    // Send HTTP refresh header
    header("Refresh: $autorefresh");

    // local url to retreive file text
    $url="http://srvbt-mon.tunel.local:8080/get_files.php";
    
    // token used to retreive file
    $token="WUipXlkmQljA2byYi52dRj7VeUs1eSliSEheVKjX9dVcoFTdZ8AbJp9UC49AyucC";

    // Get current timestamp
    $currentTimestamp = time();

    // Create CTable instance
    $table = new CTable();

    // Get Files
    $directory = '/home/sistemas/backup-config';
    $files = array_diff(scandir($directory), array('..', '.','executions.log'));

    // Read and print content of log file
    $logFileName = $directory . '/executions.log';
    $logFile = fopen($logFileName, 'r');
    $content = fread($logFile, filesize($logFileName));
    fclose($logFile);

    // Add headers to table
    $table->setHeader([
        (new CColHeader(_('Host')))->setAttribute('style', 'text-align: center; font-size: 32px;'),
        (new CColHeader(_('File')))->setAttribute('style', 'text-align: center; font-size: 32px;'),
        (new CColHeader(_('Last update')))->setAttribute('style', 'text-align: center; font-size: 32px;')
    ]);

    // Add rows to table based on array
    foreach ($files as $f) {
        $get_url = ($url . "?archivo=" . $f . "&token=" . $token );
        
        // set style custom
        echo '<style>.myButton { background: none; border: none; }</style>';

        // make button
        $link = (new CButton())
            ->addClass('myButton')
            ->addItem(
                (new CLink('Show config', $get_url))
                    ->setAttribute('style', 'text-align: center; color: white; background-color: #5bbbbc; padding: 10px; border-radius: 5px; border: none;')
            );

        // make last update date readable
        $updateDate = date("F d Y H:i:s", filemtime($directory . "/" . $f));

        // Convert last update date to timestamp
        $lastUpdateTimestamp = date(filemtime($directory . "/" . $f));

        // debug
        //echo ('current'.$currentTimestamp . ' - ');
        //echo ('last'. $lastUpdateTimestamp . ' - ');
        //$diff = $currentTimestamp - $lastUpdateTimestamp;
        //$time = 25*60*60;
        //echo ('diff'. $diff);
        //echo ('time'. $time);

        // Check if last update time is updated recently or not
        if (($currentTimestamp - $lastUpdateTimestamp > 24 * 60 * 60) || date("Y-m-d", $lastUpdateTimestamp) != date("Y-m-d")) {
            // Set red color style to col
            $updateDateCol = (new CCol($updateDate. ' >> This device is not sync'))->setAttribute('style', 'text-align: center; padding: 10px 0; color: red;');
        } else {
            // Set normal style
            $updateDateCol = (new CCol($updateDate))->setAttribute('style', 'text-align: center; padding: 10px 0;');
        }

        // add rows to table
        $table->addRow([
            (new CCol($f))->setAttribute('style', 'text-align: center; padding: 10px 0;'),
            (new CCol($link))->setAttribute('style', 'text-align: center; padding: 10px 0;'),
            //(new CCol($updateDate))->setAttribute('style', 'text-align: center; padding: 10px 0;')
            $updateDateCol
        ]);
    }

    // Create instance of CHtmlPage
    $page = new CHtmlPage();

    // Set Title
    $page->setTitle('Backup - Networking');

    // Create div with info content at the top
    $divdirectory = (new CDiv())
        ->addItem((new CDiv('Last check: ' . date("F d Y H:i:s",$currentTimestamp)))->setAttribute('style', 'display: block; font-size: 20px;'))
        ->addItem('Directory: ' . $directory)
        ->addItem((new CDiv('Items: ' . count($files)))->setAttribute('style', 'display: block; font-size: 14px;'))
        ->setAttribute('style', 'margin-bottom: 20px; background-color: #2b2b2b; padding: 10px;font-size: 14px;');
  
    $divTextLog = (new CDiv())
        ->addItem('LOG')
        ->setAttribute('style', 'margin-top: 10px; background-color: #427691; padding: 10px; width: 630px;');

    // Create div with log content at the bottom
    echo '<style>
        .myTextarea {
            width: 650px;
            min-height: 500px;
            padding: 10px;
        }
    </style>';

    $textarea = (new CTag('textarea', true, $content))
        ->setAttribute('class', 'myTextarea')
        ->setAttribute('readonly', 'readonly');

    $divLog = (new CDiv())
        ->setAttribute('style', 'min-height: 500px;')
        ->addItem($textarea);
    
    // Set margin with table and tittle
    $table->setAttribute('style', 'margin-top: 20px 20px 20px 20px;');
    
    // Add div to CHtmlPage
    $page->addItem($divdirectory);

    // Add table to CHtmlPage
    $page->addItem($table);

    // Add divLog to CHtmlPage
    $page->addItem($divTextLog);
    $page->addItem($divLog);

    // Show page
    $page->show();
?>
