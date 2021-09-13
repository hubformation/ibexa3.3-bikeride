<?php
/**
 * File containing the CreateContentCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace App\Command;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\ValueObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use League\Csv\Reader;
use eZ\Publish\Core\FieldType\Author\Author;

/**
 * this command creates a simple content object containing a title and a body.
 */
class CreateContentCommand extends Command
{
    /** @var array Difficulty */
    const DIFFICULTY = [0 => 'Beginner', 1 => 'Intermediate', 2 => 'Advanced'];

    /**
     * @var null|string
     */
    private $importUser;
    /**
     * @var ContentService
     */
    private $contentService;
    /**
     * @var LocationService
     */
    private $locationService;
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;
    /**
     * @var PermissionResolver
     */
    private $permissionResolver;
    /**
     * @var UserService
     */
    private $userService;

    /**
     * CreateContentCommand constructor.
     * @param null|string $importUser
     * @param ContentService $contentService
     * @param LocationService $locationService
     * @param ContentTypeService $contentTypeService
     * @param PermissionResolver $permissionResolver
     * @param UserService $userService
     */
    public function __construct(
        string $importUser,
        ContentService $contentService,
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        PermissionResolver $permissionResolver,
        UserService $userService
    )
    {
        parent::__construct(null);
        //...
        // IMPORTANT: set the import user...
        $this->importUser = $importUser;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName( 'training:create_content' )->setDefinition(
            array(
                new InputArgument( 'parentLocationId', InputArgument::REQUIRED, 'An existing parent location ID' ),
                new InputArgument( 'file', InputArgument::REQUIRED, 'Source CSV file' ),
            )
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void|null
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output ): int
    {

        //$repository->getPermissionResolver()->setCurrentUserReference($repository->getUserService()->loadUser( 14 ));

        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin($this->importUser)
        );

        // fetch the input arguments
        $parentLocationId = $input->getArgument( 'parentLocationId' );
        $inputFile = $input->getArgument( 'file' );

        $inputCsv = Reader::createFromPath($inputFile);
        $inputCsv->setHeaderOffset(0); //0: $headerKey => value , 1: value => $headerKey
        $inputCsv->getContent();

        foreach($inputCsv as $ride) {
            try {
                $output->writeln( 'Importing : ' . $ride['title'] );
                $contentType = $this->contentTypeService->loadContentTypeByIdentifier('ride');
                $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, 'eng-GB');

                $contentCreateStruct->setField('name', $ride['title']);
                $contentCreateStruct->setField('photo', 'steps/import_data/'.$ride['image_name']);

                $author = explode(',',$ride['author_and_email']);
                $contentCreateStruct->setField('author',
                    array(
                        new Author(array('name' => $author[0], 'email' => $author[1]))
                    )
                );

                $startingPointCoords = explode(',',$ride['starting_point_latitude_and_longitude']);
                $endingPointCoords = explode(',',$ride['ending_point_latitude_and_longitude']);

                $contentCreateStruct->setField('starting_point',array(
                        'address' => $ride['starting_point_address'],
                        'latitude' => (float) $startingPointCoords[0],
                        'longitude' => (float) $startingPointCoords[1]
                    )
                );

                $contentCreateStruct->setField('ending_point',array(
                        'address' => $ride['ending_point_address'],
                        'latitude' => (float) $endingPointCoords[0],
                        'longitude' => (float) $endingPointCoords[1]
                    )
                );

                $contentCreateStruct->setField('length',(int) $ride['length']);

                $DifficultyKey = array_search($ride['difficulty'], self::DIFFICULTY);
                $contentCreateStruct->setField('difficulty',array(
                        'selection' => $DifficultyKey
                    )
                );

                $inputXML = '<?xml version="1.0" encoding="UTF-8"?><section xmlns="http://ez.no/namespaces/ezpublish5/xhtml5/edit">'.$ride['description'].'</section>';

                $contentCreateStruct->setField('description', $inputXML);

                // instantiate a location create struct from the parent location
                $locationCreateStruct = $this->locationService->newLocationCreateStruct($parentLocationId);
                dump($locationCreateStruct);
                return Command::SUCCESS;

                // create a draft using the content and location create struct and publish it
                $draft = $this->contentService->createContent($contentCreateStruct, array($locationCreateStruct));

                dump($draft);
                return Command::SUCCESS;

                $content = $this->contentService->publishVersion($draft->versionInfo);

                // print out the content
                //dump($content);



            }
                // Content type or location not found
                // Invalid field value
                // Required field missing or empty
            catch ( Exceptions\NotFoundException | Exceptions\ContentFieldValidationException | Exceptions\ContentValidationException $e) {
                $output->writeln($e->getMessage());
            }
        }
        return 0;
    }
}
