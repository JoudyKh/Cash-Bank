<?php

namespace App\Services\Admin\Section;
use App\Models\Section;
use App\Http\Resources\SectionResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\Admin\Section\StoreSectionRequest;
use App\Http\Requests\Api\Admin\Section\UpdateSectionRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SectionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function getAll($trashOnly, Section $parentSection = null):SectionResource|AnonymousResourceCollection
    {
        if($parentSection){
            $sections = Section::where('parent_id', $parentSection->id);
        }
        else {
            $sections = Section::orderByDesc($trashOnly ? 'deleted_at' : 'created_at');
        }
        if ($trashOnly) {
            $sections->onlyTrashed();
        }
        $sections = $sections->paginate(config('app.pagination_limit'));
        return SectionResource::collection($sections);
    }
    public function store(StoreSectionRequest $request, Section $parentSection = null, $type = null):SectionResource
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->storePublicly('sections/images', 'public');
        }
        if ($parentSection)
            $data['parent_id'] = $parentSection->id;
        $data['name']['ar'] = $data['ar_name'];
        $data['name']['en'] = $data['en_name'];
        $section = Section::create($data);
        return SectionResource::make($section);
    }
    public function update(UpdateSectionRequest $request, Section $section):SectionResource
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->storePublicly('sections/images', 'public');
            if (Storage::exists("public/$section->image")) {
                Storage::delete("public/$section->image");
            }
        }
        if(isset($data['ar_name'])){
            $data['name']['ar'] =  $data['ar_name'];
        }
        if(isset($data['en_name'])){
            $data['name']['en'] = $data['en_name'];
        }
        $section->update($data);
        return SectionResource::make($section);
    }
    public function delete($id, $force = null):bool
    {
        if ($force) {
            $section = Section::onlyTrashed()->findOrFail($id);
                if(Storage::exists("public/$section->image"))
                    Storage::delete("public/$section->image");
            $section->forceDelete();
        } else {
                Section::where('id', $id)->delete();
        }
        return true;
    }
    public function restore(string|int $id):bool
    {
        $section = Section::withTrashed()->find($id);

        if ($section && $section->trashed()) {
            $section->restore();
            return true;
        }
        throw new \Exception("not found", 404); 

    }
}
