<?PHP 
  require_once("db_configuration.php");
  require_once("PHPExcel/PHPExcel.php");

/**
 * Class represents a table in a database and pulls info from that table
 */
  class Table {
    /**
     * Constructor for Table class that initializes all table values
     * @param string $tableName of table you want to want to export from database
     * @param array  $fields    specify the exact fields you want to export else exports all
     */
    function  Table ($tableName, $fields) {
      $this->tableName = $tableName;
      if (is_array($fields)) {
        $this->fields = $fields;
        $this->numFields = count($fields);
      } else {
        $this->numFields = 0;
      }
      $this->exportSql = '';
      $this->createExportSqlStatement();
      $this->pullTableInfo();
    }
    
    /**
     * Generates export sql statement for a table
     */
    function createExportSqlStatement() {
      $sql = '';
      if ($this->numFields === 0) {
         $sql = 'SELECT * FROM ' . $this->tableName;
      }
      else {
        $sql = 'SELECT ';
        $array = $this->fields;
        for($i = 0; $i < $this->numFields; $i++) {
          if ($i == $this->numFields-1) {
            $sql .= $array[$i] . ' ';
            break; 
          }
          $sql .= $array[$i] . ', ';
        }
        $sql .= 'FROM ' .$this->tableName;
      }
      $this->exportSql = $sql;
    }
    
    /**
     * Pulls all table info from database
     */
    function pullTableInfo() {
      $info = array();
      $result = run_sql($this->exportSql);
      $num_rows = $result->num_rows;
      if ($num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          array_push($info, $row);
        }
      }
      $this->tableInfo = $info;
    }
  }
  
  /**
   * Class exports tables to a specified excel workbook
   */
  class excelExporter {
    /**
     * Constructor that in that initializes all excelExporter values and parameters
     * @param array $tables   to be exported
     * @param string $fileName to be exported to
     */
    function excelExporter($tables, $fileName) {
      if (is_array($tables)) {
        $this->tables = $tables;
      } else {
        $this->tables = array();
      }
      $this->fileName = $fileName; 
      $this->objPHPExcel = new PHPExcel();
      $this->makeSheets(count($this->tables));
      $this->abcs = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O' ,'P' ,'Q' ,'R' ,'S' ,'T' , 'U', 'V' , 'W', 'X', 'Y', 'Z');
      header("Content-Type: application/vnd.ms-excel; charset=utf-8");
      header("Content-Disposition: attachment; filename=" . $fileName);
    }

    /**
     * Exports the tables to excel
     */
    function exportTables() {
      $rowIndex = 2;
      $abcs = $this->abcs;
      for($i = 0;count($this->tables) > 0; $i++) { // There are tables in the array
        $currentTable = array_shift($this->tables);
        $this->objPHPExcel->setActiveSheetIndex($i);
        $currentSheet = $this->objPHPExcel->getActiveSheet();
        $currentSheet->setTitle($currentTable->tableName);
        $this->setColumnHeaders($currentTable, $currentSheet);
        $rowIndex = 2;
        while (count($currentTable->tableInfo) > 0) { // table contains array of data
          $row = array_shift($currentTable->tableInfo);
          for ($k = 0; count($row) > 0; $k++) { // getting each row
            $currentSheet->SetCellValue(($abcs[$k] . $rowIndex), array_shift($row));
            $currentSheet->getColumnDimension($abcs[$k])->setAutoSize(true);
          }
          $rowIndex++;
        }
      }
      $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
      ob_end_clean();
      $objWriter->save('php://output');
    }

    /**
     * Creates a param number of sheets in the Excel workbook
     * @param integer $numSheets of sheets to add Excel workbook
     */
    function makeSheets($numSheets) {
      for ($i = 0; $i < $numSheets - 1; $i++) {
        $currentSheet = $this->objPHPExcel->createSheet($i);
      }
    }

    /**
     * Adds column headers to Excel sheet
     * @param Table $table to get column headers from
     * @param objPHPExcelSheet $sheet to add the headers to
     */
    function setColumnHeaders($table, $sheet) {
      $abcs = $this->abcs;
      for($i = 0; count($table->fields) > 0; $i++) {
        $header = array_shift($table->fields);
        $sheet->SetCellValue($abcs[$i] . 1, $header);
      }
    }
  }

  /**
   * Creates instance of all tables to be exported
   * @param array, of Tables
   */
  function createAllTables() {
    $wordsTable = new Table('words', array('word_id', 'word_value', 'rep_id'));
    $charTable = new Table('characters', array('word_id', 'character_index', 'character_value'));
    $puzzleTable = new Table('puzzles', array('puzzle_id', 'puzzle_name', 'creator_email'));
    $puzzleWordsTable = new Table('puzzle_words', array('puzzle_id', 'word_id', 'position_in_name'));
    return array($wordsTable, $charTable, $puzzleTable, $puzzleWordsTable);
  }

  /**
   * Creates an ExcelExporter object and calls appropriate methods to export tables
   * to an excel file
   */
  function exportAllTables() {
    $tables = createAllTables();
    $excelExporter = new excelExporter($tables, "exported_db.xlsx");
    $excelExporter->exportTables();
  }

exportAllTables();

?>
