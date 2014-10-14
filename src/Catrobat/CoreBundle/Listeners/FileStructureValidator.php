<?php

namespace Catrobat\CoreBundle\Listeners;

use Catrobat\CoreBundle\Model\ExtractedCatrobatFile;
use Symfony\Component\Finder\Finder;
use AppBundle\Exceptions\InvalidCatrobatFileException;
use AppBundle\Events\ProgramBeforeInsertEvent;

class FileStructureValidator
{
  
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file)
  {
    $finder = new Finder();
    $finder->in($file->getPath())
           ->exclude(array("sounds","images"))
           ->notPath("/^code.xml$/")
           ->notPath("/^screenshot.png$/")
           ->notPath("/^manual_screenshot.png$/")
           ->notPath("/^automatic_screenshot.png$/");
    
    if ($finder->count() > 0)
    {
      $list = array();
      foreach ($finder as $file)
      {
        $list[] = $file->getRelativePathname();
      }
      throw new InvalidCatrobatFileException("unexpected files found: " . implode(", ", $list));
    }
  }

}
