<?php

namespace Clue\React\Readline;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use BadMethodCallException;

class Readline extends EventEmitter
{
    private $loop;
    private $prompt;
    private $autocompleteCallback = null;
    private $listening = false;
    private $autohistory = true;

    public function __construct(LoopInterface $loop, $prompt = '')
    {
        $this->loop = $loop;
        $this->prompt = $prompt;

        $this->resume();
    }

    public function setAutocompleteWords(array $words)
    {
        $this->setAutocomplete(function ($word, $wordPosition) use ($words) {
            // TODO: fix me
            $wordPosition = strlen($word);

            $pre  = (string)substr($word, 0, $wordPosition);
            $post = (string)substr($word, $wordPosition);

            $ret = array();
            foreach ($words as $one) {
                if ((string)substr($one, 0, $wordPosition) === $pre/* && (string)substr($one, $wordPosition) === $post*/) {
                    $ret []= $one;
                }
            }

            //var_dump($pre, $wordPosition, $ret);

            return $ret;
        });
    }

    /**
     *
     * @param callable $callback $callback($word, $positionInWord, $line, $positionInLine)
     * @usee self::unsetAutocomplete()
     */
    public function setAutocomplete($callback)
    {
        $this->autocompleteCallback = $callback;
        readline_completion_function(array($this, 'handleAutocomplete'));
    }

    public function unsetAutocomplete()
    {
        $this->autocompleteCallback = null;
    }

    public function resume()
    {
        if ($this->listening) {
            return;
        }

        if (!function_exists('readline_callback_handler_install')) {
            throw new BadMethodCallException('Method "readline_callback_handler_install" not available, update PHP and/or enable ext-readline');;
        }

        $this->listening = true;

        readline_callback_handler_install($this->prompt, array($this, 'handleLine'));

        $this->loop->addReadStream(STDIN, function() {
            readline_callback_read_char();
        });
    }

    public function pause()
    {
        if (!$this->listening) {
            return;
        }

        readline_callback_handler_remove();
        $this->loop->removeReadStream(STDIN);
    }

    public function addHistory($line)
    {
        readline_add_history($line);
    }

    public function clearHistory()
    {
        readline_clear_history();
    }

    public function listHistory()
    {
        if (!function_exists('readline_list_history')) {
            throw new BadMethodCallException('Method "readline_list_history" not available, update PHP and/or enable ext-readline compiled against libreadline instead of libedit');;
        }

        return readline_list_history();
    }

    public function handleLine($line)
    {
        // EOF (CTRL + D)
        if ($line === null) {
            $this->emit('end', array($line));
            $this->pause();
        } else {
            $this->emit('line', array($line));

            if ($this->autohistory) {
                $this->addHistory($line);
            }
        }
    }

    public function handleAutocomplete($part, $offset)
    {
        if (!$this->autocompleteCallback) {
            return;
        }

        $info = readline_info();
        $line = substr($info['line_buffer'], 0, $info['end']);

        return call_user_func($this->autocompleteCallback, $part, $offset, $line, $info['point']);
    }
}
