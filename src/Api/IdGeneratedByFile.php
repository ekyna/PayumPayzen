<?php

namespace Ekyna\Component\Payum\Payzen\Api;

use Exception;
use Payum\Core\Exception\RuntimeException;

class IdGeneratedByFile implements TransactionIdInterface
{
    private $path;
    private $debug;

    public function __construct(
        string $path,
        bool $debug = false
    ) {
        $this->path = $path;
        $this->debug = $debug;
    }

    /**
     * @throws Exception
     */
    public function getTransactionId(): array
    {
        $this->createDirectoryPath();
        $this->path = rtrim($this->path,DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path = $this->path . 'transaction_id';

        // Create file if not exists
        if (!file_exists($path)) {
            touch($path);
            chmod($path, 0600);
        }

        $date = (new \DateTime())->format('Ymd');
        $fileDate = date('Ymd', filemtime($path));
        $isDailyFirstAccess = ($date != $fileDate);

        // Open file
        $handle = fopen($path, 'r+');
        if (false === $handle) {
            throw new RuntimeException('Failed to open the transaction ID file.');
        }
        // Lock File
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Failed to lock the transaction ID file.');
        }

        $id = 1;
        // If not daily first access, read and increment the id
        if (!$isDailyFirstAccess) {
            $id = (int)fread($handle, 6);
            $id++;
        }

        // Truncate, write, unlock and close.
        fseek($handle, 0);
        ftruncate($handle, 0);
        fwrite($handle, (string)$id);
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        if ($this->debug) {
            $id += 89000;
        }

        return [
            'vads_trans_date' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('YmdHis'),
            'vads_trans_id' => str_pad($id, 6, '0', STR_PAD_LEFT)
        ];
    }

    /**
     * Returns the directory path and creates it if not exists.
     */
    public function createDirectoryPath(): void
    {
        // Create directory if not exists
        if (!is_dir($this->path)) {
            if (!mkdir($this->path, 0755, true)) {
                throw new RuntimeException('Failed to create cache directory');
            }
        }
    }
}
