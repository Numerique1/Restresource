<?php
namespace Numerique1\Components\Restresources\Command;
use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Dumper;
/**
 * Class GenerateResourceConfigurationCommand
 * @package Numerique1\EScoping\WebBundle\Command
 */
class CreateResourceFileCommand extends ContainerAwareCommand
{

    private $doctrineHelper;

    public function __construct(DoctrineHelper $doctrineHelper, KernelInterface $kernel)
    {
        parent::__construct();
        $this->doctrineHelper = $doctrineHelper;
        $this->kernelRootDir = $kernel->getRootDir();
    }

    protected function configure()
    {
        $this->setName('rest-resources:file:create')
            ->setDescription('Generates yml file for the configuration of the entity API')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');

        $file = [];

        #Resource
        $question = new Question("<question>Please enter the name of the resource (plural)</question> <comment>(ex: users)</comment> \n > ");
        $resource = $questionHelper->ask($input, $output, $question);
        $file['resource'] = strtolower($resource);

        #Class
        $question = new Question("<question>Please provide le entity class for this resource</question> <comment>(ex: Acme\\UserBundle\\Entity\\User)</comment> \n > ");
        $entities = $this->doctrineHelper->getEntitiesForAutocomplete();
        $question->setAutocompleterValues($entities);
        $class = $questionHelper->ask($input, $output, $question);
        $file['class'] = $class;

        list($bundle, $entity) = explode('Entity', $class);
        #Actions
        $question = new Question("<question>Which actions the API must do for this resource ?</question> <fg=white;>(default: CGET,GET,POST,PATCH,DELETE)</> \n > ", 'CGET,GET,POST,PATCH,DELETE');
        $actions = $questionHelper->ask($input, $output, $question);
        $file['actions'] = explode(',', $actions);

        $_actions = $file['actions'];
        if(in_array('POST', $_actions) ||in_array('PUT', $_actions) ||in_array('PATCH', $_actions)){
            #Type
            $question = new Question("<question>Please provide a 'FormType' class to handle POST,PATCH,PUT actions</question> <fg=white;>(default: {$bundle}Form\\Type{$entity}Type)</> \n > ", "{$bundle}Form\\Type{$entity}Type");
            $type = $questionHelper->ask($input, $output, $question);
            $file['type'] = $type;
        }

        #Bundle
//        str_replace("\\", "/","{$bundle}Resources/Restresource/{$file['resource']}.resource.yml"
        $default = $this->kernelRootDir;
        $question = new Question("<info>Where we might put the file ? </info> <fg=white;>(default: {$default})</> \n > ", "{$default}");
        $path = $questionHelper->ask($input, $output, $question);

        $dumper = new Dumper();
        $yml = $dumper->dump($file, 10);
        if (!file_exists($filename = $path))
        {
            if (!is_dir($dir = dirname($filename))){
                mkdir($dir, 0777, true);
            }
            file_put_contents($filename, $yml);
        }
    }
}