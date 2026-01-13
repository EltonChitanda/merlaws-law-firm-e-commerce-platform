<?php
/*
 * SimpleXLSX php class v1.1.1
 * Lightweight XLSX reader
 * @see https://github.com/shuchkin/simplexlsx
 * Copyright (c) 2012-2023 Sergey Shuchkin
 * Licensed under the MIT license
 */

class SimpleXLSX {
	public static $error = false;
	public $workbook;
	public $sheets = [];
	public $sheetNames = [];
	private $sheetName;
	private $relationship;
	private $date1904 = false;

	const SCHEMA_RELATIONSHIP = 'http://schemas.openxmlformats.org/package/2006/relationships';
	const SCHEMA_OFFICEDOCUMENT = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument';
	const SCHEMA_SHEET = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet';
	const SCHEMA_SHAREDSTRINGS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings';
	const SCHEMA_STYLES = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles';

	public function __construct( $filename, $is_data = false, $debug = false, $skipEmptyRows = false ) {
		self::$error = false;
		$this->workbook = $this->parse( $filename, $is_data, $debug, $skipEmptyRows );
	}

	public static function parse( $filename, $is_data = false, $debug = false, $skipEmptyRows = false ) {
		$xlsx = new self( $filename, $is_data, $debug, $skipEmptyRows );

		return $xlsx->success() ? $xlsx : false;
	}

	public static function parseError() {
		return self::$error;
	}

	public function success() {
		return ( self::$error === false );
	}

	protected function parse( $filename, $is_data, $debug, $skipEmptyRows ) {
		if ( $debug ) {
			ini_set( 'display_errors', 1 );
			error_reporting( E_ALL );
		}
		$this->skipEmptyRows = $skipEmptyRows;
		if ( !$is_data ) {
			if ( !file_exists( $filename ) ) {
				self::$error = 'File not found';
				return false;
			}
			if ( !is_readable( $filename ) ) {
				self::$error = 'File not readable';
				return false;
			}
			$filename = @file_get_contents( $filename );
			if ( $filename === false ) {
				self::$error = 'Unable to read file';
				return false;
			}
		}

		$this->package = new SimpleXLSX_Package( $filename );
		if ( $this->package->error ) {
			self::$error = $this->package->error;
			return false;
		}

		$rels = $this->package->relationship( '_rels/.rels' );
		if ( !$rels ) {
			self::$error = 'Relationships not found';
			return false;
		}

		if ( empty( $rels->Relationship ) ) {
			self::$error = 'No relationships for root';
			return false;
		}
		foreach ( $rels->Relationship as $rel ) {
			if ( $rel['Type'] == self::SCHEMA_OFFICEDOCUMENT ) {
				$workbook = simplexml_load_string( $this->package->getEntryData( $rel['Target'] ) );
				if ( !$workbook ) {
					self::$error = 'Workbook could not be read';
					return false;
				}
				$this->workbook = $workbook;
				$this->sheetName = $rel['Target'];
				$this->relationship = dirname( $rel['Target'] ) . '/_rels/' . basename( $rel['Target'] ) . '.rels';
				break;
			}
		}

		if ( !$this->workbook ) {
			self::$error = 'Workbook not found in relationships';
			return false;
		}

		if ( isset( $this->workbook->workbookPr['date1904'] ) ) {
			$this->date1904 = (bool) $this->workbook->workbookPr['date1904'];
		}

		if ( isset( $this->workbook->sheets ) ) {
			foreach ( $this->workbook->sheets->sheet as $sheet ) {
				$this->sheetNames[ (string) $sheet['sheetId'] ]	= (string) $sheet['name'];
				$this->sheetIds[ (string) $sheet['sheetId'] ]	= (string) $sheet['id'];
			}
		}

		$this->sharedStrings();
		$this->styles();

		foreach ( $this->workbook->sheets->sheet as $sheet ) {
			if ( isset( $sheet['state'] ) && $sheet['state'] == 'hidden' ) {
				continue;
			}
			$this->sheets[] = $this->getSheet( (string) $sheet['name'] );
		}

		return $this->workbook;
	}

	private function sharedStrings() {
		$this->sharedstrings = [];
		$rels = $this->package->relationship( $this->relationship );
		if ( $rels && $rels->Relationship ) {
			foreach ( $rels->Relationship as $rel ) {
				if ( $rel['Type'] == self::SCHEMA_SHAREDSTRINGS ) {
					$sharedStringsXml = $this->package->getEntryData( dirname( $this->relationship ) . '/' . (string) $rel['Target'] );
					if ( $sharedStringsXml === false ) {
						continue;
					}
					$sharedStrings = simplexml_load_string( $sharedStringsXml );
					if ( $sharedStrings && $sharedStrings->si ) {
						foreach ( $sharedStrings->si as $val ) {
							$entry = '';
							if ( isset( $val->t ) ) {
								$entry = (string) $val->t;
							} elseif ( isset( $val->r ) ) {
								foreach ( $val->r as $run ) {
									$entry .= (string) $run->t;
								}
							}
							$this->sharedstrings[] = $entry;
						}
					}
				}
			}
		}
	}

	private function styles() {
		$this->styles = [];
		$rels = $this->package->relationship( $this->relationship );
		if ( $rels && $rels->Relationship ) {
			foreach ( $rels->Relationship as $rel ) {
				if ( $rel['Type'] == self::SCHEMA_STYLES ) {
					$stylesXml = $this->package->getEntryData( dirname( $this->relationship ) . '/' . (string) $rel['Target'] );
					if ( $stylesXml === false ) {
						continue;
					}
					$styles = simplexml_load_string( $stylesXml );
					if ( $styles && $styles->cellXfs && $styles->cellXfs->xf ) {
						foreach ( $styles->cellXfs->xf as $xf ) {
							$this->styles[] = [
								'numFmtId' => (int) $xf['numFmtId'],
							];
						}
					}
				}
			}
		}
	}

	public function rows( $worksheetIndex = 0 ) {
		if ( !isset( $this->sheets[ $worksheetIndex ] ) ) {
			return [];
		}
		return $this->sheets[ $worksheetIndex ];
	}

	public function sheetNames() {
		return array_values( $this->sheetNames );
	}

	public function getSheet( $name ) {
		foreach ( $this->workbook->sheets->sheet as $sheet ) {
			if ( (string) $sheet['name'] === $name ) {
				$rid = (string) $sheet['id'];
				$rels = $this->package->relationship( $this->relationship );
				if ( $rels && $rels->Relationship ) {
					foreach ( $rels->Relationship as $rel ) {
						if ( (string) $rel['Id'] == $rid && $rel['Type'] == self::SCHEMA_SHEET ) {
							$sheetXml = $this->package->getEntryData( dirname( $this->relationship ) . '/' . (string) $rel['Target'] );
							if ( $sheetXml === false ) {
								continue;
							}
							$sheetData = simplexml_load_string( $sheetXml );
							return $this->parseSheetData( $sheetData );
						}
					}
				}
			}
		}
		return [];
	}

	private function parseSheetData( $sheetData ) {
		$rows = [];
		if ( isset( $sheetData->sheetData->row ) ) {
			foreach ( $sheetData->sheetData->row as $row ) {
				$rowIndex = (int) $row['r'] - 1;
				if ( !isset( $rows[ $rowIndex ] ) ) {
					$rows[ $rowIndex ] = [];
				}
				$currentRow = &$rows[ $rowIndex ];
				foreach ( $row->c as $c ) {
					$cellIndex = $this->getColumnIndexFromName( (string) $c['r'] );
					$currentRow[ $cellIndex ] = $this->value( $c );
				}
				if ( !$this->skipEmptyRows ) {
					ksort( $currentRow );
				}
			}
		}
		if ( !$this->skipEmptyRows ) {
			ksort( $rows );
			$rows = array_values( $rows );
			foreach ( $rows as &$row ) {
				ksort( $row );
				$row = array_values( $row );
			}
		} else {
			ksort( $rows );
			$ordered = [];
			foreach ( $rows as $row ) {
				ksort( $row );
				$ordered[] = array_values( $row );
			}
			$rows = $ordered;
		}
		return $rows;
	}

	private function getColumnIndexFromName( $cell ) {
		if ( preg_match( '/([A-Z]+)(\d+)/', $cell, $matches ) ) {
			$letters = $matches[1];
			$column = 0;
			$length = strlen( $letters );
			for ( $i = 0; $i < $length; $i++ ) {
				$column = $column * 26 + ( ord( $letters[$i] ) - ord( 'A' ) + 1 );
			}
			return $column - 1;
		}
		return 0;
	}

	private function value( $cell ) {
		$value = isset( $cell->v ) ? (string) $cell->v : '';
		$attributes = $cell->attributes();
		$type = isset( $attributes['t'] ) ? (string) $attributes['t'] : '';
		$style = isset( $attributes['s'] ) ? (int) $attributes['s'] : null;

		switch ( $type ) {
			case 's':
				return isset( $this->sharedstrings[ (int) $value ] ) ? $this->sharedstrings[ (int) $value ] : $value;
			case 'b':
				return $value === '1';
			case 'inlineStr':
				return isset( $cell->is->t ) ? (string) $cell->is->t : $value;
			default:
				if ( $style !== null && isset( $this->styles[ $style ] ) ) {
					$styleInfo = $this->styles[ $style ];
					$numFmtId = $styleInfo['numFmtId'];
					if ( $this->isDateTimeFormat( $numFmtId ) ) {
						return $this->excelToTimestamp( $value );
					}
				}
				if ( is_numeric( $value ) ) {
					return $value + 0;
				}
				return $value;
		}
	}

	private function isDateTimeFormat( $numFmtId ) {
		$builtinFormats = [ 14, 15, 16, 17, 18, 19, 20, 21, 22, 45, 46, 47 ];
		return in_array( $numFmtId, $builtinFormats, true );
	}

	private function excelToTimestamp( $value ) {
		$value = (float) $value;
		if ( $this->date1904 ) {
			$value += 1462;
		}
		$timestamp = ( $value - 25569 ) * 86400;
		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}
}

class SimpleXLSX_Package {
	public $error = false;
	private $entries = [];

	public function __construct( $data ) {
		$this->unzip( $data );
	}

	private function unzip( $data ) {
		$zip = new ZipArchive();
		if ( $zip->open( 'data://application/octet-stream;base64,' . base64_encode( $data ) ) === true ) {
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				$entry = $zip->getNameIndex( $i );
				$this->entries[ $entry ] = $zip->getFromIndex( $i );
			}
			$zip->close();
		} else {
			$this->error = 'Unable to open ZIP archive';
		}
	}

	public function getEntryData( $name ) {
		return $this->entries[ $name ] ?? false;
	}

	public function relationship( $filename ) {
		if ( !isset( $this->entries[ $filename ] ) ) {
			return false;
		}
		$xml = simplexml_load_string( $this->entries[ $filename ] );
		if ( $xml === false ) {
			return false;
		}
		return $xml;
	}
}

