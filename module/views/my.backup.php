<?php
    use CTable;
    use CCol;
    use CColHeader;
    use CLink;
    use CHtmlPage;
    use CDiv;
    use CButton;
    use CTag;

    // local url to retreive file text
    $url="http://ZABBIX:8080/get_files.php";
    
    // token used to retreive file
    $token="XXXXXXXX";

    // Create CTable instance
    $table = new CTable();

    // Get Files
    $directory = '/home/user/backup-config';
    $files = array_diff(scandir($directory), array('..', '.'));

    // Leer y mostrar el contenido del archivo
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
                (new CLink($f, $get_url))
                    ->setAttribute('style', 'text-align: center; color: white; background-color: green; padding: 10px; border-radius: 5px; border: none;')
            );

        // make last update date readable
        $updateDate = date("F d Y H:i:s.", filemtime($directory . "/" . $f));

        // add rows to table
        $table->addRow([
            (new CCol($f))->setAttribute('style', 'text-align: center; padding: 10px 0;'),
            (new CCol($link))->setAttribute('style', 'text-align: center; padding: 10px 0;'),
            (new CCol($updateDate))->setAttribute('style', 'text-align: center; padding: 10px 0;')
        ]);
    }

    // Create instance of CHtmlPage
    $page = new CHtmlPage();

    // Set Title
    $page->setTitle(_('Backup - Networking'));

    // Create div with info content at the top
    $divdirectory = (new CDiv())
        ->addItem('Directory: ' . $directory)
        ->addItem((new CDiv('Items: ' . count($files)))->setAttribute('style', 'display: block;'))
        ->setAttribute('style', 'margin-bottom: 20px; background-color: #2b2b2b; padding: 10px;');
  
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
