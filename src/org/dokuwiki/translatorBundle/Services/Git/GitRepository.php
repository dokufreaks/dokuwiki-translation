<?php

namespace org\dokuwiki\translatorBundle\Services\Git;

class GitRepository {

    public static $PULL_CHANGED = 'changed';
    public static $PULL_UNCHANGED = 'unchanged';

    private $gitService;

    private $path;

    public function __construct(GitService $gitService, $path) {
        $this->gitService = $gitService;
        $this->path = $path;
    }

    public function cloneFrom($source, $destination) {
        return $this->run('clone', $source, $destination);
    }

    public function pull($remote = 'origin', $branch = 'master') {
        $result = $this->run('pull', $remote, $branch);

        // empty result -> new, contains already up2date -> unchanged, else updated
        if (strstr($result->getOutput(), 'Already up-to-date') !== false) {
            return GitRepository::$PULL_UNCHANGED;
        }
        return GitRepository::$PULL_CHANGED;
    }

    public function remoteAdd($name, $path) {
        return $this->run('remote', 'add', $name, $path);
    }

    public function commit($message, $author) {
        return $this->run('commit', '-m', $message, '--author', $author);
    }

    public function createPatch($revision = 'HEAD~1') {
        $result = $this->run('format-patch', '--stdout', $revision);
        return $result->getOutput();
    }

    public function add($file) {
        return $this->run('add', $file);
    }

    private function run() {
        $arguments = func_get_args();
        $command = array($this->gitService->getGitBinary());

        foreach ($arguments as $argument) {
            $command[] = escapeshellarg($argument);
        }

        $command = implode(' ', $command);
        $result = $this->runCommand($command);

        if ($result->getExitCode()) {
            throw new GitException($result->getExitCode() . $result->getError() . $result->getExitCode());
        }

        return $result;
    }

    private function runCommand($command) {
        $descriptorspec = array(
            1 => array("pipe", "w"), // stdout
            2 => array("pipe", "w") // stderr
        );

        if (file_exists($this->path)) {
            $process = proc_open($command, $descriptorspec, $pipes, $this->path);
        } else {
            $process = proc_open($command, $descriptorspec, $pipes);
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $exitCode = trim(proc_close($process));

        return new ProgrammCallResult($stderr, $exitCode, $stdout);
    }
}