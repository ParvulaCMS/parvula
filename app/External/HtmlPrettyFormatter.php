<?php

namespace External;

use League\BooBoo\Util;
use League\BooBoo\Formatter\AbstractFormatter;

class HtmlPrettyFormatter extends AbstractFormatter
{

    /**
     * @var Util\Formatter;
     */
    protected $formatter;

    public function format(\Exception $e)
    {
        $this->inspector = new Util\Inspector($e);

        if ($e instanceof \ErrorException) {
            return $this->handleErrors($e);
        }

        return $this->formatExceptions($e);
    }

    public function handleErrors(\ErrorException $e)
    {
        $severity = $this->determineSeverityTextValue($e->getSeverity());
        $type = 'Error (' . $severity . ')';

        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();

        return $this->getHtml($type, $message, $file, $line);
    }

    protected function formatExceptions(\Exception $e)
    {
        $type = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();

        return $this->getHtml($type, $message, $file, $line);;
    }

    protected function processFrame($frame)
    {
        $function = $frame->getClass() ?: '';
        $function .= $frame->getClass() && $frame->getFunction() ? ":" : "";
        $function .= $frame->getFunction() ?: '';

        $filename = ($frame->getFile() ?: '<#unknown>');

        $line = (int)$frame->getLine();

        $code = $this->getFileCode($filename, $line);

        return [$function, $filename, $line, $code];
    }

    protected function getFileCode($file, $line, $arround = 10) {
        $file = file($file);

        $out = '';
        for ($i = -$arround; $i < $arround; ++$i) {
            if (isset($file[$line + $i])) {
                $out .= rtrim($file[$line + $i], "\r\n");
                if ($i === -1) {
                    $out .= ' // << Error';
                }
                $out .= PHP_EOL;
            }
        }

        return $out;
    }

    protected function printArray(array $arr) {
        $out = '<table>';

        foreach ($arr as $key => $val) {
            $out .= '<tr>';
            $out .= '<td>' . $key . '</td>';
            $out .= '<td>' . str_replace(PHP_EOL, ' ', print_r($val, true)) . '</td>';
            $out .= '</tr>';
        }

        return $out . '</table>';
    }

    protected function getHtml($errorType, $message, $filename, $line)
    {
        $frames = $this->inspector->getFrames();

        $code = $this->getFileCode($filename, $line);

        ob_start();
        require 'template.php';
        $out = ob_get_contents();
        ob_end_clean();

        return $out;
    }
}
