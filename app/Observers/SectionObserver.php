<?php

namespace App\Observers;

use App\Models\Section;

class SectionObserver
{
    public function deleting(Section $section)
    {
        foreach ($section->subSections as $subSection) {
            $subSection->delete();
        }
    }
}
