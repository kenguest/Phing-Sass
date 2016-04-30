<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Paul Stuart <pstuart2@gmail.com>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 */

/**
 * Pull in Task class.
 */
require_once 'phing/Task.php';

/**
 * Executes Sass for a particular fileset.
 * 
 * If the sass executable is not available, but scssphp is, then use that instead.
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Paul Stuart <pstuart2@gmail.com>
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @version  Release: $Id$
 * @link     SassTask.php
 */
class SassTask extends Task
{

    protected $style = 'nested';
    protected $trace = false;
    protected $unixnewlines = true;
    protected $encoding = 'utf-8';

    /**
     * Contains the path info of our file to allow us to parse.
     *
     * @var array
     */
    protected $pathInfo = null;

    /**
     * The Sass executable.
     *
     * @var string
     */
    protected $executable = 'sass';

    /**
     * The ext type we are looking for when Verifyext is set to true.
     *
     * More than likely should be "scss" or "sass".
     *
     * @var string
     */
    protected $extfilter = '';

    /**
     * This flag means 'note errors to the output, but keep going'
     *
     * @var bool
     */
    protected $failonerror = true;

    /**
     * The fileset we will be running Sass on.
     *
     * @var array
     */
    protected $filesets = [];

    /**
     * Additional flags to pass to sass.
     *
     * @var string
     */
    protected $flags = '';

    /**
     * Indicates if we want to keep the directory structure of the files.
     *
     * @var bool
     */
    protected $keepsubdirectories = true;

    /**
     * When true we will remove the current file ext.
     *
     * @var bool
     */
    protected $removeoldext = true;

    /**
     * The new ext our files will have.
     *
     * @var string
     */
    protected $newext = 'css';
    /**
     * The path to send our output files to.
     *
     * If not defined they will be created in the same directory the
     * input is from.
     *
     * @var string
     */
    protected $outputpath = '';

    /**
     * Sets the failonerror flag. Default: true
     *
     * @param string $failonerror Jenkins style boolean value
     *
     * @access public
     * @return void
     */
    public function setFailonerror($failonerror)
    {
        $this->failonerror = StringHelper::booleanValue($failonerror);
    }

    /**
     * Sets the executable to use for sass. Default: sass
     *
     * The default assumes sass is in your path. If not you can provide the full
     * path to sass.
     *
     * @param string $executable Name/path of sass executable
     *
     * @access public
     * @return void
     */
    public function setExecutable($executable)
    {
        $this->executable = $executable;
    }

    /**
     * Sets the extfilter. Default: <none>
     *
     * This will filter the fileset to only process files that match
     * this extension. This could also be done with the fileset.
     *
     * @param string $extfilter Extension to filter for.
     *
     * @access public
     * @return void
     */
    public function setExtfilter($extfilter)
    {
        $this->extfilter = trim($extfilter, ' .');
    }

    /**
     * Additional flags to pass to sass.
     *
     * Command will be:
     * sass {$flags} {$inputfile} {$outputfile}
     *
     * @param string $flags List of flags accepted by sass.
     *
     * @access public
     * @return void
     */
    public function setFlags($flags)
    {
        $this->flags = trim($flags);
    }

    /**
     * Sets the removeoldext flag. Default: true
     *
     * This will cause us to strip the existing extension off the output
     * file.
     *
     * @param string $removeoldext Jenkins style boolean value
     *
     * @access public
     * @return void
     */
    public function setRemoveoldext($removeoldext)
    {
        $this->removeoldext = StringHelper::booleanValue($removeoldext);
    }

    /**
     * Set default encoding
     *
     * @param string $encoding Default encoding to use.
     *
     * @return void
     */
    public function setEncoding($encoding)
    {
        $encoding = trim($encoding);
        if ($encoding !== '') {
            $this->flags .= " --default-encoding $encoding";
        } else {
            $this->flags = str_replace(
                ' --default-encoding ' . $this->encoding,
                '',
                $this->flags
            );
        }
        $this->encoding = $encoding;
    }

    /**
     * Sets the newext value. Default: css
     *
     * This is the extension we will add on to the output file regardless
     * of if we remove the old one or not.
     *
     * @param string $newext New extension to use, e.g. css
     *
     * @access public
     * @return void
     */
    public function setNewext($newext)
    {
        $this->newext = trim($newext, ' .');
    }

    /**
     * Sets the outputpath value. Default: <none>
     *
     * This will force the output path to be something other than
     * the path of the fileset used.
     *
     * @param string $outputpath Path name
     *
     * @access public
     * @return void
     */
    public function setOutputpath($outputpath)
    {
        $this->outputpath = rtrim(trim($outputpath), DIRECTORY_SEPARATOR);
    }

    /**
     * Sets the keepsubdirectories value. Default: true
     *
     * When set to true we will keep the directory structure. So any input
     * files in subdirectories will have their output file in that same
     * sub-directory. If false, all output files will be put in the path
     * defined by outputpath or in the directory top directory of the fileset.
     *
     * @param bool $keepsubdirectories Jenkins style boolean
     *
     * @access public
     * @return void
     */
    public function setKeepsubdirectories($keepsubdirectories)
    {
        $this->keepsubdirectories = StringHelper::booleanValue($keepsubdirectories);
    }

    /**
     * Nested creator, creates a FileSet for this task
     *
     * @return FileSet The created fileset object
     */
    public function createFileSet()
    {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num - 1];
    }

    /**
     * Whether to just check syntax.
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setCheck($value)
    {
        $check = StringHelper::booleanValue($value);
        $this->check = $check;
        if ($check) {
            $this->flags .= ' --check ';
        } else {
            $this->flags = str_replace(' --check ', '', $this->flags);
        }
    }

    /**
     * Set style to compact
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setCompact($value)
    {
        $compress = StringHelper::booleanValue($value);
        if ($compress) {
            $this->flags = str_replace(' --style ' . $this->style, '', $this->flags);
            $this->flags .= ' --style compact';
            $this->style = 'compact';
        }
    }

    /**
     * Set style to compressed
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setCompressed($value)
    {
        $compress = StringHelper::booleanValue($value);
        if ($compress) {
            $this->flags = str_replace(' --style ' . $this->style, '', $this->flags);
            $this->flags .= ' --style compressed';
            $this->style = 'compressed';
        }
    }

    /**
     * Set style to expanded
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setExpand($value)
    {
        $expand = StringHelper::booleanValue($value);
        if ($expand) {
            $this->flags = str_replace(' --style ' . $this->style, '', $this->flags);
            $this->flags .= ' --style expanded';
            $this->style = 'expanded';
        }
    }

    /**
     * Whether to force recompiled when --update is used.
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setForce($value)
    {
        $force = StringHelper::booleanValue($value);
        $this->force = $force;
        if ($force) {
            $this->flags .= ' --force ';
        } else {
            $this->flags = str_replace(' --force ', '', $this->flags);
        }
    }

    /**
     * Whether to cache parsed sass files.
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setNoCache($value)
    {
        $noCache = StringHelper::booleanValue($value);
        $this->noCache = $noCache;
        if ($noCache) {
            $this->flags .= ' --no-cache ';
        } else {
            $this->flags = str_replace(' --no-cache ', '', $this->flags);
        }
    }

    /**
     * Specify SASS import path
     *
     * @param string $path Import path.
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->flags .= "--load-path $path";
    }

    /**
     * Set output style.
     *
     * @param mixed $style Style.
     *
     * @return void
     */
    public function setStyle($style)
    {
        $style = strtolower($style);
        switch($style) {
        case 'nested':
        case 'compact':
        case 'compressed':
        case 'expanded':
            $this->flags = str_replace(" --style $this->style", '', $this->flags);
            $this->style = $style;
            $this->flags .= " --style $style";
            break;
        default:
            $this->log("Style $style ignored", Project::MSG_INFO);
        }
    }

    /**
     * Set trace option.
     *
     * IE: Whether to output a stack trace on error.
     *
     * @param string $trace Jenkins style boolean value
     *
     * @return void
     */
    public function setTrace($trace)
    {
        $this->trace = StringHelper::booleanValue($trace);
        if ($this->trace) {
            $this->flags .= ' --trace ';
        } else {
            $this->flags = str_replace(' --trace ', '', $this->flags);
        }
    }

    /**
     * Whether to use unix-style newlines.
     *
     * @param string $newlines Jenkins style boolean value
     *
     * @return void
     */
    public function setUnixnewlines($newlines)
    {
        $unixnewlines = StringHelper::booleanValue($newlines);
        $this->unixnewlines = $unixnewlines;
        if ($unixnewlines) {
            $this->flags .= ' --unix-newlines ';
        } else {
            $this->flags = str_replace(' --unix-newlines ', '', $this->flags);
        }
    }

    /**
     * Init: pull in the PEAR System class
     *
     * @access public
     * @return void
     */
    public function init()
    {
        include_once 'System.php';
        @include_once 'vendor/autoload.php';
    }

    /**
     * Our main execution of the task.
     *
     * @throws BuildException
     * @throws Exception
     *
     * @access public
     * @return void
     */
    public function main()
    {
        if (strlen($this->executable) < 0) {
            throw new BuildException("'executable' must be defined.");
        }

        if (empty($this->filesets)) {
            throw new BuildException(
                "Missing either a nested fileset or attribute 'file'"
            );
        }

        $useScssphp = false;
        if (System::which($this->executable) === false) {
            // make two attempts to load in leafo's SCSS PHP Compiler.
            $v = @include_once "vendor/leafo/scssphp/scss.inc.php";
            if ($v === false) {
                $v = @include_once "scssphp/scss.inc.php";
            }
            if ($v === false) {
                throw new BuildException(
                    sprintf(
                        "%s not found. Install sass or leafo.",
                        $this->executable
                    )
                );
            } else {
                $useScssphp = true;
                $scss = new Leafo\ScssPhp\Compiler();
                if ($this->style) {
                    $ucStyle = ucfirst(strtolower($this->style));
                    $scss->setFormatter('Leafo\\ScssPhp\\Formatter\\' . $ucStyle);
                }
                if ($this->encoding) {
                    $scss->setEncoding($this->encoding);
                }
            }
        }

        $specifiedOutputPath = (strlen($this->outputpath) > 0);

        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $files = $ds->getIncludedFiles();
            $dir = $fs->getDir($this->project)->getPath();

            // If output path isn't defined then set it to the path of our fileset.
            if ($specifiedOutputPath === false) {
                $this->outputpath = $dir;
            }

            foreach ($files as $file) {
                $fullFilePath = $dir . DIRECTORY_SEPARATOR . $file;
                $this->pathInfo = pathinfo($file);

                $run = true;
                switch(strtolower($this->pathInfo['extension'])) {
                case 'scss':
                case 'sass':
                    break;
                default:
                    $this->log('Ignoring ' . $file, Project::MSG_DEBUG);
                    $run = false;
                }

                if ($run
                    && ($this->extfilter === ''
                    || $this->extfilter === $this->pathInfo['extension'])
                ) {
                    $outputFile = $this->buildOutputFilePath($file);
                    $output = null;

                    if (!$useScssphp) {
                        try {
                            $output = $this->executeCommand(
                                $fullFilePath,
                                $outputFile
                            );
                            if ($this->failonerror && $output[0] !== 0) {
                                throw new BuildException(
                                    "Result returned as not 0. Result: {$output[0]}",
                                    Project::MSG_INFO
                                );
                            }
                        } catch (Exception $e) {
                            if ($this->failonerror) {
                                throw $e;
                            } else {
                                $this->log(
                                    "Result: {$output[0]}",
                                    Project::MSG_INFO
                                );
                            }
                        }
                    } else {
                        $this->log(
                            sprintf("Compiling '%s' via scssphp", $fullFilePath),
                            Project::MSG_INFO
                        );
                        $input = file_get_contents($fullFilePath);
                        try {
                            $out = $scss->compile($input, $fullFilePath);
                            if ($out !== '') {
                                $success = file_put_contents($outputFile, $out);
                                if ($success) {
                                    $this->log(
                                        sprintf(
                                            "'%s' compiled and written to '%s'"
                                            $fullFilePath,
                                            $outputFile
                                        ),
                                        Project::MSG_VERBOSE
                                    );
                                }
                            } else {
                                $this->log('Compilation resulted in empty string')
                            }
                        } catch (Exception $ex) {
                            if ($this->failonerror) {
                                throw new BuildException($ex->getMessage());
                            } else {
                                $this->log($ex->getMessage(), Project::MSG_ERR);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Builds the full path to the output file based on our settings.
     *
     * @return string
     *
     * @access protected
     */
    protected function buildOutputFilePath()
    {
        $outputFile = $this->outputpath.DIRECTORY_SEPARATOR;

        $subpath = trim($this->pathInfo['dirname'], ' .');

        if ($this->keepsubdirectories === true && strlen($subpath) > 0) {
            $outputFile .= $subpath . DIRECTORY_SEPARATOR;
        }

        $outputFile .= $this->pathInfo['filename'];

        if (!$this->removeoldext) {
            $outputFile .= '.' . $this->pathInfo['extension'];
        }

        if (strlen($this->newext) > 0) {
            $outputFile .= '.' . $this->newext;
        }

        return $outputFile;
    }

    /**
     * Executes the command and returns return code and output.
     *
     * @param string $inputFile Input file
     * @param string $outputFile Output file
     * *
     * @return array array(return code, array with output)
     * @throws BuildException
     * @access protected
     */
    protected function executeCommand($inputFile, $outputFile)
    {
        // Prevent over-writing existing file.
        if ($inputFile == $outputFile) {
            throw new BuildException('Input file and output file are the same!');
        }

        $output = [];
        $return = null;

        $fullCommand = $this->executable;

        if (strlen($this->flags) > 0) {
            $fullCommand .= " {$this->flags}";
        }

        $fullCommand .= " {$inputFile} {$outputFile}";

        $this->log("Executing: {$fullCommand}", Project::MSG_INFO);
        exec($fullCommand, $output, $return);

        return [$return, $output];
    }
}
