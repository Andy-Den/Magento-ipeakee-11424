<?php
/**
 * This file was generated by the ConvertToLegacy class in bronto-legacy.
 * The purpose of the conversion was to maintain PSR-0 compliance while
 * the main development focuses on modern styles found in PSR-4.
 *
 * For the original:
 * @see src/Bronto/Logger/Handler/Console.php
 */


/**
 * Bronto_Logger_Handler_Console handler will output logs to the console.
 *
 * @author Philip Cali <philip.cali@bronto.com>
 */
class Bronto_Logger_Handler_Console implements Bronto_Logger_LogHandler
{
    private static $_translate = array(
        Bronto_Logger_LogInterface::DEBUG => 'DEBUG',
        Bronto_Logger_LogInterface::INFO => 'INFO',
        Bronto_Logger_LogInterface::WARN => 'WARNING',
        Bronto_Logger_LogInterface::ERROR => 'ERROR',
    );

    protected $_format;

    /**
     * Populate the console logger with a date format
     *
     * @param string $format
     */
    public function __construct($format = 'c')
    {
        $this->_format = $format;
    }

    /**
     * Write the log to the output buffer
     *
     * @param int $level
     * @param string $message
     * @param array $backtrace
     * @return void
     */
    public function write($level, $message, $backtrace)
    {
        echo $this->_prefix($level, $backtrace) . '- ' . $message . "n";
    }

    /**
     * Format the log prefix with relavent infomation
     *
     * @param int $level
     * @param array $backtrace
     * @return string
     */
    protected function _prefix($level, $backtrace)
    {
        $fileAndLine = ' ';
        if (array_key_exists('class', $backtrace) && !empty($backtrace['class'])) {
            $fileAndLine = sprintf(' %s::%s:%d ',
                $backtrace['class'],
                $backtrace['function'],
                $backtrace['line']);
        }
        if ($fileAndLine == ' ' && array_key_exists('file', $backtrace)) {
            $paths = pathinfo($backtrace['file']);
            $fileAndLine = sprintf(' %s:%d ', $paths['basename'], $backtrace['line']);
        }
        return sprintf('%s %s%s',
            date($this->_format),
            self::$_translate[$level],
            $fileAndLine);
    }
}
