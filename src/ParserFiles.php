<?php

namespace sndsgd\yaml;

class ParserFiles
{
    /**
     * A regex used to match the line and column information in error messages
     *
     * @var string
     */
    const ERROR_REGEX = "/\\(line (\d+), column (\d+)\\)/";

    private $contents = "";
    private $linesPerFile = [];

    public function addFile(string $path): void
    {
        $file = \sndsgd\Fs::file($path);
        $yaml = $file->read();
        if ($yaml === false) {
            throw new \Exception("failed to parse YAML file; ".$file->getError());
        }

        # ensure that the yaml ends with a newline
        if (!\sndsgd\Str::endsWith($yaml, "\n")) {
            $yaml .= "\n";
        }

        $this->linesPerFile[$path] = substr_count($yaml, "\n") + 1;
        $this->contents .= $yaml;
    }

    public function getContents(): string
    {
        if (empty($this->linesPerFile)) {
            throw new \LogicException(
                "files must be added before contents can be retrieved"
            );
        }

        return $this->contents;
    }

    /**
     * Update an error message to retrieve the correct file and line
     *
     * @param string $message The error message from `yaml_parse()`
     * @return string
     */
    public function getErrorMessage(string $message): string
    {
        if (count($this->linesPerFile) === 1) {
            return $message;
        }

        if (preg_match_all(self::ERROR_REGEX, $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as [$search, $errorLine, $column]) {
                $errorLine = (int) $errorLine;
                $totalLineCount = 0;
                foreach ($this->linesPerFile as $path => $lineCount) {

                    if ($totalLineCount + $lineCount < $errorLine) {
                        $totalLineCount += $lineCount - 1;
                        continue;
                    }

                    $line = $errorLine - $totalLineCount;

                    $replace = "($path, line $line, column $column)";
                    $message = str_replace($search, $replace, $message);
                }
            }
        }

        return $message;
    }
}
