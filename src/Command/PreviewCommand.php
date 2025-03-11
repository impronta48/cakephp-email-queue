<?php
declare(strict_types=1);

//cakephp5
// namespace EmailQueue\Shell;
namespace EmailQueue\Command;

//cakephp5
// use Cake\Console\Shell;
//cakephp5
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use EmailQueue\Model\Table\EmailQueueTable;

// cakephp5
// class PreviewShell extends Shell
class PreviewCommand extends Command
{
    /**
     * Main
     *
     * @return bool|int|null|void
     */
    // cakephp5
    //  public function main()
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        Configure::write('App.baseUrl', '/');

        $conditions = [];
        //cakephp5
        // if ($this->args) {
        //     $conditions['id IN'] = $this->args;
        // }
        if ($args) {
            $conditions['id IN'] = $args->getArguments();
        }

        $emailQueue = TableRegistry::getTableLocator()->get('EmailQueue', ['className' => EmailQueueTable::class]);
        //cakephp5
        // $emails = $emailQueue->find()->where($conditions)->toList();
        $emails = $emailQueue->find()->where($conditions)->toArray();

        if (!$emails) {
            //cakephp5
            // $this->out('No emails found');
            $io->out('No emails found');

            return static::CODE_ERROR;
        }

        //cakephp5 
        // $this->clear();
        foreach ($emails as $i => $email) {
            if ($i) {
                //cakephp5 
                // $this->in('Hit a key to continue');
                $io->ask('Hit a key to continue');
                //cakephp5 
                // $this->clear();
            }
            //cakephp5
            // $this->out('Email :' . $email->id);
            $io->out('Email :' . $email->id);
            $this->preview($email, $io);
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Preview email
     *
     * @param array $e email data
     * @return void
     */
    //cakephp5
    // public function preview($e)
    public function preview($e, ConsoleIo $io)
    {
        $configName = $e['config'];
        $template = $e['template'];
        $layout = $e['layout'];
        $headers = empty($e['headers']) ? [] : (array)$e['headers'];
        $theme = empty($e['theme']) ? '' : (string)$e['theme'];

        $email = new Mailer($configName);

        if (!empty($e['attachments'])) {
            $email->setAttachments($e['attachments']);
        }

        $email->setTransport('Debug')
            ->setTo($e['email'])
            ->setSubject($e['subject'])
            ->setEmailFormat($e['format'])
            ->addHeaders($headers)
            ->setMessageId(false)
            ->setReturnPath($email->getFrom())
            ->setViewVars($e['template_vars']);

        $email->viewBuilder()
            ->setTheme($theme)
            ->setTemplate($template)
            ->setLayout($layout);

        $return = $email->deliver();

        //cakephp5
        // $this->out('Content:');
        // $this->hr();
        // $this->out($return['message']);
        // $this->hr();
        // $this->out('Headers:');
        // $this->hr();
        // $this->out($return['headers']);
        // $this->hr();
        // $this->out('Data:');
        // $this->hr();
        // debug($e['template_vars']);
        // $this->hr();
        // $this->out('');
        $io->out('Content: ');
        $io->hr();
        $io->out($return['message']);
        $io->hr();
        $io->out('Headers:');
        $io->hr();
        $io->out($return['headers']);
        $io->hr();
        $io->out('Data:');
        $io->hr();
        debug($e['template_vars']);
        $io->hr();
        $io->out('');
    }
}
