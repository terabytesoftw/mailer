<?php
namespace Yiisoft\Mailer;

/**
 * FileMailer is a mock mailer that save email messages in files instead of sending them.
 */
class FileMailer extends BaseMailer
{
    /**
     * The path where message files located.
     * @var string $path
     */
    private $path = '/tmp/mails';
    
    /**
     * Returns path.
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Sets path.
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @var callable a PHP callback that return a file name which will be used to 
     * save the email message.
     * If not set, the file name will be generated based on the current timestamp.
     *
     * The signature of the callback is:
     *
     * ```php
     * function ($mailer, $message)
     * ```
     */
    private $filenameCallback;

    /**
     * Sets filename callback.
     * @param callable $callback
     */
    public function setFilenameCallback(Callable $callback): void
    {
        $this->filenameCallback = $callback;
    }

    /**
     * @return string the filename for saving the message.
     */
    protected function generateMessageFilename(): string
    {
        $time = microtime(true);

        return date('Ymd-His-', $time) . sprintf('%04d', (int) (($time - (int) $time) * 10000)) . '-' . sprintf('%04d', mt_rand(0, 10000)) . '.eml';
    }

    /**
     * {@inheritdoc}
     */
    protected function sendMessage(MessageInterface $message): void
    {
        if ($this->filenameCallback !== null) {
            $filename = call_user_func($this->filenameCallback, $this, $message);
        } else {
            $filename = $this->generateMessageFilename();
        }
        $filename = $this->path . DIRECTORY_SEPARATOR . $filename;
        $filepath = dirname($filename);
        if (!is_dir($filepath)) {
            mkdir($filepath, 0777, true);
        }
        file_put_contents($filename, $message->toString());
    }
}