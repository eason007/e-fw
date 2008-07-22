<?php
class TEMPLATE_PHPLIB_CLASS {

	var $classname = 'Template';
	var $debug    = false;
	var $root     = '.';
	var $file     = array();
	var $varkeys  = array();
	var $varvals  = array();
	var $unknowns = 'keep';
	var $halt_on_error  = 'yes';
	var $lastError     = '';

	/******************************************************************************
	* Class constructor. May be called with two optional parameters.
	* The first parameter sets the template directory the second parameter
	* sets the policy regarding handling of unknown variables.
	*
	* usage: Template([string $root = '.'], [string $unknowns = 'remove'])
	*
	* @param     $root        path to template directory
	* @param     $string      what to do with undefined variables
	* @see       setRoot
	* @see       setUnknowns
	* @access    public
	* @return    void
	*/
	function __construct($root = '.', $unknowns = 'remove') {
		if ($this->debug & 4) {
			echo '<p><b>Template:</b> root = $root, unknowns = $unknowns</p>\n';
		}
		$this->setRoot($root);
		$this->setUnknowns($unknowns);
	}


	/******************************************************************************
	* Checks that $root is a valid directory and if so sets this directory as the
	* base directory from which templates are loaded by storing the value in
	* $this->root. Relative filenames are prepended with the path in $this->root.
	*
	* Returns true on success, false on error.
	*
	* usage: setRoot(string $root)
	*
	* @param     $root         string containing new template directory
	* @see       root
	* @access    public
	* @return    boolean
	*/
	function setRoot($root) {
		if ($this->debug & 4) {
			echo '<p><b>setRoot:</b> root = $root</p>\n';
		}
		if (!is_dir($root)) {
			$this->halt('setRoot: $root is not a directory.');
			return false;
		}

		$this->root = $root;
		return true;
	}


	/******************************************************************************
	* Sets the policy for dealing with unresolved variable names.
	*
	* unknowns defines what to do with undefined template variables
	* 'remove'   = remove undefined variables
	* 'comment'  = replace undefined variables with comments
	* 'keep'     = keep undefined variables
	*
	* Note: 'comment' can cause unexpected results when the variable tag is embedded
	* inside an HTML tag, for example a tag which is expected to be replaced with a URL.
	*
	* usage: setUnknowns(string $unknowns)
	*
	* @param     $unknowns         new value for unknowns
	* @see       unknowns
	* @access    public
	* @return    void
	*/
	function setUnknowns($unknowns = 'remove') {
		if ($this->debug & 4) {
			echo '<p><b>unknowns:</b> unknowns = $unknowns</p>\n';
		}
		$this->unknowns = $unknowns;
	}


	/******************************************************************************
	* Defines a filename for the initial value of a variable.
	*
	* It may be passed either a varname and a file name as two strings or
	* a hash of strings with the key being the varname and the value
	* being the file name.
	*
	* The new mappings are stored in the array $this->file.
	* The files are not loaded yet, but only when needed.
	*
	* Returns true on success, false on error.
	*
	* usage: setFile(array $filelist = (string $varname => string $filename))
	* or
	* usage: setFile(string $varname, string $filename)
	*
	* @param     $varname      either a string containing a varname or a hash of varname/file name pairs.
	* @param     $filename     if varname is a string this is the filename otherwise filename is not required
	* @access    public
	* @return    boolean
	*/
	function setFile($varname, $filename = '') {
		if (!is_array($varname)) {
			if ($this->debug & 4) {
				echo '<p><b>setFile:</b> (with scalar) varname = $varname, filename = $filename</p>\n';
			}
			if ($filename == '') {
				$this->halt('setFile: For varname $varname filename is empty.');
				return false;
			}
			$this->file[$varname] = $this->filename($filename);
		} else {
			reset($varname);
			while(list($v, $f) = each($varname)) {
				if ($this->debug & 4) {
					echo '<p><b>setFile:</b> (with array) varname = $v, filename = $f</p>\n';
				}
				if ($f == '') {
					$this->halt('setFile: For varname $v filename is empty.');
					return false;
				}
				$this->file[$v] = $this->filename($f);
			}
		}
		return true;
	}


	/******************************************************************************
	* A variable $parent may contain a variable block defined by:
	* &lt;!-- BEGIN $varname --&gt; content &lt;!-- END $varname --&gt;. This function removes
	* that block from $parent and replaces it with a variable reference named $name.
	* The block is inserted into the varkeys and varvals hashes. If $name is
	* omitted, it is assumed to be the same as $varname.
	*
	* Blocks may be nested but care must be taken to extract the blocks in order
	* from the innermost block to the outermost block.
	*
	* Returns true on success, false on error.
	*
	* usage: setBlock(string $parent, string $varname, [string $name = ''])
	*
	* @param     $parent       a string containing the name of the parent variable
	* @param     $varname      a string containing the name of the block to be extracted
	* @param     $name         the name of the variable in which to store the block
	* @access    public
	* @return    boolean
	*/
	function setBlock($parent, $varname, $name = '') {
		if ($this->debug & 4) {
			echo '<p><b>setBlock:</b> parent = $parent, varname = $varname, name = $name</p>\n';
		}
		if (!$this->loadfile($parent)) {
			$this->halt('setBlock: unable to load $parent.');
			return false;
		}
		if ($name == '') {
			$name = $varname;
		}

		$str = $this->getVar($parent);
		$reg = '/[ \t]*<!--\s+BEGIN $varname\s+-->\s*?\n?(\s*.*?\n?)\s*<!--\s+END $varname\s+-->\s*?\n?/sm';
		preg_match_all($reg, $str, $m);
		$str = preg_replace($reg, '{' . '$name}', $str);
		$this->setVar($varname, $m[1][0]);
		$this->setVar($parent, $str);
		return true;
	}


	/******************************************************************************
	* This functions sets the value of a variable.
	*
	* It may be called with either a varname and a value as two strings or an
	* an associative array with the key being the varname and the value being
	* the new variable value.
	*
	* The function inserts the new value of the variable into the $varkeys and
	* $varvals hashes. It is not necessary for a variable to exist in these hashes
	* before calling this function.
	*
	* An optional third parameter allows the value for each varname to be appended
	* to the existing variable instead of replacing it. The default is to replace.
	* This feature was introduced after the 7.2d release.
	*
	*
	* usage: setVar(string $varname, [string $value = ''], [boolean $append = false])
	* or
	* usage: setVar(array $varname = (string $varname => string $value), [mixed $dummy_var], [boolean $append = false])
	*
	* @param     $varname      either a string containing a varname or a hash of varname/value pairs.
	* @param     $value        if $varname is a string this contains the new value for the variable otherwise this parameter is ignored
	* @param     $append       if true, the value is appended to the variable's existing value
	* @access    public
	* @return    void
	*/
	function setVar($varname, $value = '', $append = false) {
		if (!is_array($varname)) {
			if (!empty($varname)) {
				if ($this->debug & 1) {
					printf('<b>setVar:</b> (with scalar) <b>%s</b> = '%s'<br>\n', $varname, htmlentities($value));
				}
				$this->varkeys[$varname] = '/'.$this->varname($varname).'/';
				if ($append && isset($this->varvals[$varname])) {
					$this->varvals[$varname] .= $value;
				} else {
					$this->varvals[$varname] = $value;
				}
			}
		} else {
			reset($varname);
			while(list($k, $v) = each($varname)) {
				if (!empty($k)) {
					if ($this->debug & 1) {
						printf('<b>setVar:</b> (with array) <b>%s</b> = '%s'<br>\n', $k, htmlentities($v));
					}
					$this->varkeys[$k] = '/'.$this->varname($k).'/';
					if ($append && isset($this->varvals[$k])) {
						$this->varvals[$k] .= $v;
					} else {
						$this->varvals[$k] = $v;
					}
				}
			}
		}
	}


	/******************************************************************************
	* This functions clears the value of a variable.
	*
	* It may be called with either a varname as a string or an array with the
	* values being the varnames to be cleared.
	*
	* The function sets the value of the variable in the $varkeys and $varvals
	* hashes to ''. It is not necessary for a variable to exist in these hashes
	* before calling this function.
	*
	*
	* usage: clearVar(string $varname)
	* or
	* usage: clearVar(array $varname = (string $varname))
	*
	* @param     $varname      either a string containing a varname or an array of varnames.
	* @access    public
	* @return    void
	*/
	function clearVar($varname) {
		if (!is_array($varname)) {
			if (!empty($varname)) {
				if ($this->debug & 1) {
					printf('<b>clearVar:</b> (with scalar) <b>%s</b><br>\n', $varname);
				}
				$this->setVar($varname, '');
			}
		} else {
			reset($varname);
			while(list($k, $v) = each($varname)) {
				if (!empty($v)) {
					if ($this->debug & 1) {
						printf('<b>clearVar:</b> (with array) <b>%s</b><br>\n', $v);
					}
					$this->setVar($v, '');
				}
			}
		}
	}


	/******************************************************************************
	* This functions unsets a variable completely.
	*
	* It may be called with either a varname as a string or an array with the
	* values being the varnames to be cleared.
	*
	* The function removes the variable from the $varkeys and $varvals hashes.
	* It is not necessary for a variable to exist in these hashes before calling
	* this function.
	*
	*
	* usage: unsetVar(string $varname)
	* or
	* usage: unsetVar(array $varname = (string $varname))
	*
	* @param     $varname      either a string containing a varname or an array of varnames.
	* @access    public
	* @return    void
	*/
	function unsetVar($varname) {
		if (!is_array($varname)) {
			if (!empty($varname)) {
				if ($this->debug & 1) {
					printf('<b>unsetVar:</b> (with scalar) <b>%s</b><br>\n', $varname);
				}
				unset($this->varkeys[$varname]);
				unset($this->varvals[$varname]);
			}
		} else {
			reset($varname);
			while(list($k, $v) = each($varname)) {
				if (!empty($v)) {
					if ($this->debug & 1) {
						printf('<b>unsetVar:</b> (with array) <b>%s</b><br>\n', $v);
					}
					unset($this->varkeys[$v]);
					unset($this->varvals[$v]);
				}
			}
		}
	}


	/******************************************************************************
	* This function fills in all the variables contained within the variable named
	* $varname. The resulting value is returned as the function result and the
	* original value of the variable varname is not changed. The resulting string
	* is not 'finished', that is, the unresolved variable name policy has not been
	* applied yet.
	*
	* Returns: the value of the variable $varname with all variables substituted.
	*
	* usage: subst(string $varname)
	*
	* @param     $varname      the name of the variable within which variables are to be substituted
	* @access    public
	* @return    string
	*/
	function subst($varname) {
		$varvals_quoted = array();
		if ($this->debug & 4) {
			echo '<p><b>subst:</b> varname = $varname</p>\n';
		}
		if (!$this->loadfile($varname)) {
			$this->halt('subst: unable to load $varname.');
			return false;
		}

		// quote the replacement strings to prevent bogus stripping of special chars
		reset($this->varvals);
		while(list($k, $v) = each($this->varvals)) {
			$varvals_quoted[$k] = preg_replace(array('/\\\\/', '/\$/'), array('\\\\\\\\', '\\\\$'), $v);
		}

		$str = $this->getVar($varname);
		$str = preg_replace($this->varkeys, $varvals_quoted, $str);
		return $str;
	}


	/******************************************************************************
	* This is shorthand for print $this->subst($varname). See subst for further
	* details.
	*
	* Returns: always returns false.
	*
	* usage: psubst(string $varname)
	*
	* @param     $varname      the name of the variable within which variables are to be substituted
	* @access    public
	* @return    false
	* @see       subst
	*/
	function psubst($varname) {
		if ($this->debug & 4) {
			echo '<p><b>psubst:</b> varname = $varname</p>\n';
		}
		print $this->subst($varname);

		return false;
	}


	/******************************************************************************
	* The function substitutes the values of all defined variables in the variable
	* named $varname and stores or appends the result in the variable named $target.
	*
	* It may be called with either a target and a varname as two strings or a
	* target as a string and an array of variable names in varname.
	*
	* The function inserts the new value of the variable into the $varkeys and
	* $varvals hashes. It is not necessary for a variable to exist in these hashes
	* before calling this function.
	*
	* An optional third parameter allows the value for each varname to be appended
	* to the existing target variable instead of replacing it. The default is to
	* replace.
	*
	* If $target and $varname are both strings, the substituted value of the
	* variable $varname is inserted into or appended to $target.
	*
	* If $handle is an array of variable names the variables named by $handle are
	* sequentially substituted and the result of each substitution step is
	* inserted into or appended to in $target. The resulting substitution is
	* available in the variable named by $target, as is each intermediate step
	* for the next $varname in sequence. Note that while it is possible, it
	* is only rarely desirable to call this function with an array of varnames
	* and with $append = true. This append feature was introduced after the 7.2d
	* release.
	*
	* Returns: the last value assigned to $target.
	*
	* usage: parse(string $target, string $varname, [boolean $append])
	* or
	* usage: parse(string $target, array $varname = (string $varname), [boolean $append])
	*
	* @param     $target      a string containing the name of the variable into which substituted $varnames are to be stored
	* @param     $varname     if a string, the name the name of the variable to substitute or if an array a list of variables to be substituted
	* @param     $append      if true, the substituted variables are appended to $target otherwise the existing value of $target is replaced
	* @access    public
	* @return    string
	* @see       subst
	*/
	function parse($target, $varname, $append = false) {
		if (!is_array($varname)) {
			if ($this->debug & 4) {
				echo '<p><b>parse:</b> (with scalar) target = $target, varname = $varname, append = $append</p>\n';
			}
			$str = $this->subst($varname);
			if ($append) {
				$this->setVar($target, $this->getVar($target) . $str);
			} else {
				$this->setVar($target, $str);
			}
		} else {
			reset($varname);
			while(list($i, $v) = each($varname)) {
				if ($this->debug & 4) {
					echo '<p><b>parse:</b> (with array) target = $target, i = $i, varname = $v, append = $append</p>\n';
				}
				$str = $this->subst($v);
				if ($append) {
					$this->setVar($target, $this->getVar($target) . $str);
				} else {
					$this->setVar($target, $str);
				}
			}
		}

		if ($this->debug & 4) {
			echo '<p><b>parse:</b> completed</p>\n';
		}
		return $str;

	}


	/******************************************************************************
	* This is shorthand for print $this->parse(...) and is functionally identical.
	* See parse for further details.
	*
	* Returns: always returns false.
	*
	* usage: pparse(string $target, string $varname, [boolean $append])
	* or
	* usage: pparse(string $target, array $varname = (string $varname), [boolean $append])
	*
	* @param     $target      a string containing the name of the variable into which substituted $varnames are to be stored
	* @param     $varname     if a string, the name the name of the variable to substitute or if an array a list of variables to be substituted
	* @param     $append      if true, the substituted variables are appended to $target otherwise the existing value of $target is replaced
	* @access    public
	* @return    false
	* @see       parse
	*/
	function pparse($target, $varname, $append = false) {
		if ($this->debug & 4) {
			echo '<p><b>pparse:</b> passing parameters to parse...</p>\n';
		}
		print $this->finish($this->parse($target, $varname, $append));
		return false;
	}


	/******************************************************************************
	* This function returns an associative array of all defined variables with the
	* name as the key and the value of the variable as the value.
	*
	* This is mostly useful for debugging. Also note that $this->debug can be used
	* to echo all variable assignments as they occur and to trace execution.
	*
	* Returns: a hash of all defined variable values keyed by their names.
	*
	* usage: getVars()
	*
	* @access    public
	* @return    array
	* @see       $debug
	*/
	function getVars() {
		if ($this->debug & 4) {
			echo '<p><b>getVars:</b> constructing array of vars...</p>\n';
		}
		reset($this->varkeys);
		while(list($k, $v) = each($this->varkeys)) {
			$result[$k] = $this->getVar($k);
		}
		return $result;
	}


	/******************************************************************************
	* This function returns the value of the variable named by $varname.
	* If $varname references a file and that file has not been loaded yet, the
	* variable will be reported as empty.
	*
	* When called with an array of variable names this function will return a a
	* hash of variable values keyed by their names.
	*
	* Returns: a string or an array containing the value of $varname.
	*
	* usage: getVar(string $varname)
	* or
	* usage: getVar(array $varname)
	*
	* @param     $varname     if a string, the name the name of the variable to get the value of, or if an array a list of variables to return the value of
	* @access    public
	* @return    string or array
	*/
	function getVar($varname) {
		if (!is_array($varname)) {
			if (isset($this->varvals[$varname])) {
				$str = $this->varvals[$varname];
			} else {
				$str = '';
			}
			if ($this->debug & 2) {
				printf ('<b>getVar</b> (with scalar) <b>%s</b> = '%s'<br>\n', $varname, htmlentities($str));
			}
			return $str;
		} else {
			reset($varname);
			while(list($k, $v) = each($varname)) {
				if (isset($this->varvals[$v])) {
					$str = $this->varvals[$v];
				} else {
					$str = '';
				}
				if ($this->debug & 2) {
					printf ('<b>getVar:</b> (with array) <b>%s</b> = '%s'<br>\n', $v, htmlentities($str));
				}
				$result[$v] = $str;
			}
			return $result;
		}
	}


	/******************************************************************************
	* This function returns a hash of unresolved variable names in $varname, keyed
	* by their names (that is, the hash has the form $a[$name] = $name).
	*
	* Returns: a hash of varname/varname pairs or false on error.
	*
	* usage: getUndefined(string $varname, string $keyword)
	*
	* @param     $varname     a string containing the name the name of the variable to scan for unresolved variables
	* @param $varname闂傚鍋勫ú銈夊箠濮椻偓婵＄绠涘☉妯哄挤濡炪倖鐗楅悷銈囪姳?keyword濠电偞鍨堕幐鎼佸箹椤愩倖顫曢柟瀵稿仜缁剁偤鎮楅棃娑欏暈闁?
	* @access    public
	* @return    array
	*/
	function getUndefined($varname, $keyword = '') {
		if ($this->debug & 4) {
			echo '<p><b>getUndefined:</b> varname = $varname</p>\n';
		}
		if(!$this->getVar($varname)) {
			if (!$this->loadfile($varname)) {
				$this->halt('getUndefined: unable to load $varname.');
				return false;
			}
		}
		$keyword = str_replace('(', '\(',str_replace(')', '\)', $keyword));
		preg_match_all('/{(('.$keyword.')[^ \t\r\n}]+)}/', $this->getVar($varname), $m);
		//print_r($m);
		$m = $m[1];
		if (!is_array($m)) {
			return false;
		}

		reset($m);
		while(list($k, $v) = each($m)) {
			if (!isset($this->varkeys[$v])) {
				if ($this->debug & 4) {
					echo '<p><b>getUndefined:</b> undefined: $v</p>\n';
				}
				$result[$v] = $v;
			}
		}

		if (count($result)) {
			return $result;
		} else {
			return false;
		}
	}


	/******************************************************************************
	* This function returns the finished version of $str. That is, the policy
	* regarding unresolved variable names will be applied to $str.
	*
	* Returns: a finished string derived from $str and $this->unknowns.
	*
	* usage: finish(string $str)
	*
	* @param     $str         a string to which to apply the unresolved variable policy
	* @access    public
	* @return    string
	* @see       setUnknowns
	*/
	function finish($str) {
		switch ($this->unknowns) {
			case 'keep':
			break;

			case 'remove':
			$str = preg_replace('/{[^ \t\r\n}]+}/', '', $str);
			break;

			case 'comment':
			$str = preg_replace('/{([^ \t\r\n}]+)}/', '<!-- Template variable \\1 undefined -->', $str);
			break;
		}

		return $str;
	}


	/******************************************************************************
	* This function prints the finished version of the value of the variable named
	* by $varname. That is, the policy regarding unresolved variable names will be
	* applied to the variable $varname then it will be printed.
	*
	* usage: p(string $varname , blooean $gz )
	*
	* @param     $varname     a string containing the name of the variable to finish and print
	* @param     $gz			闂備礁鎼€氱兘宕规导鏉戠畾濞撴埃鍋撶€规洏鍨婚埀顒婄秵閸撴稓妲?
	* @access    public
	* @return    void
	* @see       setUnknowns
	* @see       finish
	*/
	function p($varname , $gz = false) {

		/***************************************************************************
		* 濠电儑绲藉ù鍌炲窗濡ゅ懎鏋佸┑鍌氭啞閸庡孩銇勯弮鍌氫壕闁?			闂佽娴烽弫鎼併€佹繝鍕偨妞ゆ挶鍨圭粈鍕煕濠靛棗顏╅柡浣哥埣閹宕烽褎鏁紓浣虹帛椤洭骞冮幍顔绘勃闁芥ê顦遍鑽ょ磽?闂備礁鎲″缁樻叏閹绢喖绠甸柍鍝勫€搁閬嶆煟濡寧顏犵紓鍫濐煼濮婃椽顢曢妶鍛€婚柣?
		* 濠电儑绲藉ù鍌炲窗濡ゅ懎鏋佸┑鍌滎焾缁秹鏌曟径娑㈡闁?			2004-12-9
		* 濠电儑绲藉ù鍌炲窗濡ゅ懎鏋侀柛蹇氬亹椤?			peter
		* 闂備胶绮〃鍛存偋婵犲偊鑰?				1.0
		* 闂備礁鎲￠…鍥窗鎼淬劌鑸归悗鐢电《閸?
		echo str_replace(urlencode('}'),'}',str_replace(urlencode('{'),'{',$this->finish($this->getVar($varname))));
		* 闂備胶绮划宥咁熆濮椻偓瀹曨剛鈧數纭堕崑?
		global $GZIP;
		echo str_replace(urlencode('}'),'}',str_replace(urlencode('{'),'{',$this->finish($this->getVar($varname))));
		if($gz) {
		$GZIP->gzDocOut();
		}
		***************************************************************************/

		if($gz) {
			ob_start();
			ob_implicit_flush(0);
			echo str_replace(urlencode('}'),'}',str_replace(urlencode('{'),'{',$this->finish($this->getVar($varname))));
			GZIP_CLASS::gzDocOut();
			unset($varname);			
			exit();
		} else {
			echo str_replace(urlencode('}'),'}',str_replace(urlencode('{'),'{',$this->finish($this->getVar($varname))));
			unset($varname);
			exit();
		}		
	}


	/******************************************************************************
	* This function returns the finished version of the value of the variable named
	* by $varname. That is, the policy regarding unresolved variable names will be
	* applied to the variable $varname and the result returned.
	*
	* Returns: a finished string derived from the variable $varname.
	*
	* usage: get(string $varname)
	*
	* @param     $varname     a string containing the name of the variable to finish
	* @access    public
	* @return    void
	* @see       setUnknowns
	* @see       finish
	*/
	function get($varname) {
		return $this->finish($this->getVar($varname));
	}


	/******************************************************************************
	* When called with a relative pathname, this function will return the pathname
	* with $this->root prepended. Absolute pathnames are returned unchanged.
	*
	* Returns: a string containing an absolute pathname.
	*
	* usage: filename(string $filename)
	*
	* @param     $filename    a string containing a filename
	* @access    private
	* @return    string
	* @see       setRoot
	*/
	function filename($filename) {
		if ($this->debug & 4) {
			echo '<p><b>filename:</b> filename = $filename</p>\n';
		}
		if (substr($filename, 0, 1) != '/') {
			$filename = $this->root.'/'.$filename;
		}
		if (!file_exists($filename)) {
			$this->halt('filename: file $filename does not exist.');
		}

		return $filename;
	}


	/******************************************************************************
	* This function will construct a regexp for a given variable name with any
	* special chars quoted.
	*
	* Returns: a string containing an escaped variable name.
	*
	* usage: varname(string $varname)
	*
	* @param     $varname    a string containing a variable name
	* @access    private
	* @return    string
	*/
	function varname($varname) {
		return preg_quote('{'.$varname.'}');
	}


	/******************************************************************************
	* If a variable's value is undefined and the variable has a filename stored in
	* $this->file[$varname] then the backing file will be loaded and the file's
	* contents will be assigned as the variable's value.
	*
	* Note that the behaviour of this function changed slightly after the 7.2d
	* release. Where previously a variable was reloaded from file if the value
	* was empty, now this is not done. This allows a variable to be loaded then
	* set to '', and also prevents attempts to load empty variables. Files are
	* now only loaded if $this->varvals[$varname] is unset.
	*
	* Returns: true on success, false on error.
	*
	* usage: loadfile(string $varname)
	*
	* @param     $varname    a string containing the name of a variable to load
	* @access    private
	* @return    boolean
	* @see       setFile
	*/
	function loadfile($varname) {
		if ($this->debug & 4) {
			echo '<p><b>loadfile:</b> varname = $varname</p>\n';
		}
		if (!isset($this->file[$varname])) {
			// $varname does not reference a file so return
			if ($this->debug & 4) {
				echo '<p><b>loadfile:</b> varname $varname does not reference a file</p>\n';
			}
			return true;
		}

		if (isset($this->varvals[$varname])) {
			// will only be unset if varname was created with setFile and has never been loaded
			// $varname has already been loaded so return
			if ($this->debug & 4) {
				echo '<p><b>loadfile:</b> varname $varname is already loaded</p>\n';
			}
			return true;
		}
		$filename = $this->file[$varname];

		/* use @file here to avoid leaking filesystem information if there is an error*/
		$str = implode('', @file($filename));
		if (empty($str)) {
			$this->halt('loadfile: While loading $varname, $filename does not exist or is empty.');
			return false;
		}

		if ($this->debug & 4) {
			printf('<b>loadfile:</b> loaded $filename into $varname<br>\n');
		}
		$this->setVar($varname, $str);

		return true;
	}


	/******************************************************************************
	* This function is called whenever an error occurs and will handle the error
	* according to the policy defined in $this->halt_on_error. Additionally the
	* error message will be saved in $this->lastError.
	*
	* Returns: always returns false.
	*
	* usage: halt(string $msg)
	*
	* @param     $msg         a string containing an error message
	* @access    private
	* @return    void
	* @see       $halt_on_error
	*/
	function halt($msg) {
		$this->lastError = $msg;

		if ($this->halt_on_error != 'no') {
			$this->haltmsg($msg);
		}

		if ($this->halt_on_error == 'yes') {
			die('<b>Halted.</b>');
		}

		return false;
	}


	/******************************************************************************
	* This function prints an error message.
	* It can be overridden by your subclass of Template. It will be called with an
	* error message to display.
	*
	* usage: haltmsg(string $msg)
	*
	* @param     $msg         a string containing the error message to display
	* @access    public
	* @return    void
	* @see       halt
	*/
	function haltmsg($msg) {
		printf('<b>Template Error:</b> %s<br>\n', $msg);
	}

}
?>
