<?php

namespace App\Services\General\Storage\File;
use App\Models\File;
use App\Models\Country;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Store files and their metadata.
     *
     * @param \Illuminate\Http\UploadedFile[] $files
     * @param string $folder
     * @param string $storage_disk
     * @param string|null $modelType
     * @param int|null $modelId
     * 
     * @return null
     * 
     * @throws \Throwable
     */
    public function bulkInsert($files , $folder = 'files' ,$storage_disk = 'public' , ?string $modelType = null, ?int $modelId = null)
    {            
        if($files == null)return null ;
        
        try {
            
            $filesData = [];

            foreach ($files as $file) {
            
                $name =  Str::random(40) . '.' . $file->getClientOriginalExtension();
                $name = str_replace('/' , '' , $name);
                
                $publicPath = "/{$folder}/{$name}" ;
                $path = "{$storage_disk}/{$folder}/{$name}" ;
                
                $path = str_replace('//' , '/' , $path);
                $publicPath = str_replace('//' , '/' , $publicPath);
                
                Storage::put($path, file_get_contents($file->getRealPath()));

                // Get the URL of the file
                $url = Storage::url($path);

                $filePath = storage_path("app/{$path}");
                
                $filesData[] = [
                    'model_type' => $modelType ,
                    'model_id' => $modelId ,
                    'name' => $file->getClientOriginalName() ,
                    'path' => $publicPath,
                    'url' => request()->getSchemeAndHttpHost() . $url,
                    'type' => $file->getMimeType() ,
                    'extension' => $file->getClientOriginalExtension() ,
                    'size' => filesize($filePath) ,
                ];
            }

            File::insert($filesData) ;

            return ;
            
        } catch (\Throwable $th) {
            
            foreach($filesData as $data)
            {
                if(Storage::disk($storage_disk)->exists($data['path']))
                {
                    Storage::disk($storage_disk)->delete($data['path']) ;
                }
            }
            throw $th;
        }
    }

    public function bulkInsertTransaction($files , string $folder = 'files' ,string $storage_disk = 'public' , ?string $modelType = null, ?int $modelId = null):void
    {
        DB::transaction(function()use($files ,$folder ,$storage_disk ,$modelType ,$modelId){
            $this->bulkInsert($files ,$folder ,$storage_disk ,$modelType ,$modelId) ;
        });
    }

    public function bulkDelete(array $fileIds):?bool
    {
        return File::whereIn('id' , $fileIds)->delete() ;
    }

    public function bulkDeleteByModel($modelType , $modelId):void
    {
        File::withTrashed()
            ->where('model_type' , $modelType)
            ->where('model_id' , $modelId)
            ->delete() ;
    }
    public function bulkForceDelete(array $fileIds):?bool
    {
        $paths = File::withTrashed()->whereIn('id' , $fileIds)->pluck('path') ;
        
        Storage::disk('public')->delete($paths) ;

        return File::withTrashed()->whereIn('id' , $fileIds)->forceDelete() ;
    }
    public function bulkForceDeleteByModel(string $modelType ,string|int $modelId):?bool
    {
        $files = File::withTrashed()
        ->where('model_type' , $modelType)
        ->where('model_id' , $modelId) ;

        $filesArray = $files->pluck('path')->toArray() ;

        $files->forceDelete() ;
        
        Storage::disk('public')->delete($filesArray) ;
    }
}
