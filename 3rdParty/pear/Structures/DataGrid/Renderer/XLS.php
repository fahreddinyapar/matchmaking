<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at                              |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Andrew Nagy <asnagy@webitecture.org>                         |
// +----------------------------------------------------------------------+
//
// $Id: XLS.php,v 1.5 2004/05/04 01:15:34 asnagy Exp $

require_once 'Spreadsheet/Excel/Writer.php';

/**
 * Structures_DataGrid_Renderer_XLS Class
 *
 * @version  $Revision: 1.5 $
 * @author   Andrew S. Nagy <asnagy@webitecture.org>
 * @access   public
 * @package  Structures_DataGrid
 * @category Structures
 */
class Structures_DataGrid_Renderer_XLS
{
    var $_dg;
    var $_workbook;
    var $_worksheet;
    var $_filename = 'spreadsheet.xls';

    /**
     * Constructor
     *
     * Build default values
     *
     * @access public
     */
    function Structures_DataGrid_Renderer_XLS()
    {
        $this->_workbook = new Spreadsheet_Excel_Writer();
        $this->setFilename();
        $this->_worksheet =& $this->_workbook->addWorksheet();
    }

    function setFilename($filename = 'spreadsheet.xls')
    {
        $this->_filename = $filename;
        $this->_workbook->send($filename);
    }

    function render(&$dg)
    {
        $this->_dg = &$dg;

        $this->_buildHeader();
        $this->_buildBody();
        $this->_workbook->close();
    }

    /**
     * Handles building the header of the DataGrid
     *
     * @access  private
     * @return  void
     */
    function _buildHeader()
    {
        $cnt = 0;
        foreach ($this->_dg->columnSet as $column) {
            //Define Content
            $str = $column->columnName;
            $this->_worksheet->write(0, $cnt, $str);
            $cnt++;
        }
    }

    /**
     * Handles building the body of the DataGrid
     *
     * @access  private
     * @return  void
     */
    function _buildBody()
    {
        if (count($this->_dg->recordSet)) {

            // Determine looping values
            if ($this->_dg->page > 1) {
                $begin = ($this->_dg->page - 1) * $this->_dg->rowLimit;
                $limit = $this->_dg->page * $this->_dg->rowLimit;
            } else {
                $begin = 0;
                if ($this->_dg->rowLimit == null) {
                    $limit = count($this->_dg->recordSet);
                } else {
                    $limit = $this->_dg->rowLimit;
                }
            }

            // Begin loop
            for ($i = $begin; $i < $limit; $i++) {
                $cnt = 0;
                $row = $this->_dg->recordSet[$i];
                foreach ($this->_dg->columnSet as $column) {
                    $rowCnt = ($i-$begin)+1;

                    // Build Content
                    if ($column->formatter != null) {
                        $content = $column->formatter($row);
                    } elseif ($column->fieldName == null) {
                        if ($column->autoFill != null) {
                            $content = $column->autoFill;
                        } else {
                            $content = $column->columnName;
                        }
                    } else {
                        $content = $row[$column->fieldName];
                    }

                    $this->_worksheet->write($rowCnt, $cnt, $content);

                    $cnt++;
                }
            }
        }
    }

}

?>
