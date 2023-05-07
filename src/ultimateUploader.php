<?php

namespace ultimateUploader;

/***********************************************************
* #### PHP - Ultimate Image Uploader Class ####
***********************************************************/

class ultimateUploader{
    
    /**
    * data response
    */
    public $data = [
        'message'   => '',
        'status'    => 0,
        'file'      => null,
        'ext'       => null
    ];
    
    /**
    * all error response
    */
    public $allError;
    
    /**
    * base_url / domain url
    */
    public $base_url;
    
    /**
    * server base dir
    */
    public $base_dir;
    
    /**
    * pass other params to attribute to 
    * gain access to them globally
    */
    public $attribute;

    /**
    * mime Type
    */
    private $mimeType;
    
    /**
    * extension Type
    */
    private $extensionType;
    
    /**
    * uploaded folder with image source
    */
    private $folder;
    
    /**
    * Image
    */
    private $image;
    
    /**
    * Image Compressor Settings
    */
    private $settings;
    
    /**
    * Success response tracking 
    */
    private $success = false;

    /**
    * image upload run
    */
    private $send = false;

    /**
    * error handler and response
    */
    private $error;

    /**
    * internal build property
    */
    private $internal = [
        'size'              => null,
        'size_limit'        => 0,
        'count'             => 0,
        'rearrange'         => null,
        'data'              => [],
        'raw_upload'        => [],
        'folder_upload'     => [],
        'folder_real_path'  => [],
        'folder_upload_url' => [],
        'ext_files'         => [],
        'time'              => 0
    ];
    
    
    /**
    * Constructor
    * @param errorDisallowed|null   index array - error to dis*allow
    * @param base_dir|null          base directory (needed) for upload
    * @param base_url|null          base url
    * 
    */
    public function __construct(array $errorDisallowed = [], ?array $attribute = null) 
    {
        /**
        * base root directory path
        */
        $this->base_dir = $this->optionDir();

        /**
        * base url/domain - full url path
        */
        $this->base_url = $this->optionUrl();

        /**
        * gain access to global attributes
        */
        $this->attribute = $attribute;
        
        /**
        * Error for public consumption
        */
        $this->allError = [
            '400' => "-> ERROR_400 - no file upload",
            '401' => "-> ERROR_401 - select file to upload",
            '402' => "-> ERROR_402 - File upload size is bigger than allowed size limit",
            '403' => "-> ERROR_403 - Maximum file allowed exceeded ",
            '404' => "-> ERROR_404 - Uploaded file format not allowed",
            '405' => "-> ERROR_405 - Image size allowed error",
            '500' => "-> ERROR_500 - Input file `name[]` must be passed as an array"
        ];

        /**
        * Private Errors for internal usage
        */
        $this->error = [
            '400' => 400,
            '401' => 401,
            '402' => 402,
            '403' => 403,
            '404' => 404,
            '405' => 405,
            '500' => 500,
            '200' => 200
        ];

        /**
        * Remove non-allowed error
        */
        $this->filterError($errorDisallowed);
    }


    /**
    * set non existing method
    * @param setDir|setDirectory| function call to set optional base dir 
    * @param setUrl|setURL| function call to set optional base dir 
    * @usage ->setURL('www.domain.com') | ->setDir('path_to_dir')
    */
    public function __call( $key, $value )
    {
        /**
        * base root directory path setting
        */
        if(in_array($key, ['setDir', 'setDirectory'])){
            $this->base_dir = $this->optionDir(@$value[0]);
        }

        /**
        * base url path setting
        */
        if(in_array($key, ['setUrl', 'setURL'])){
            $this->base_url = $this->optionUrl(@$value[0]);
        }
    }
    
    
    /**
    * Image upload run
    * @param filename           string - the html input file name (image).
    * @param folder_create      string - for creating folder (default, year, month, day)
    * @param upload_dir         string - for dir upload folder (image/new | images)
    * @param type               string - for file mime type (video, audio, files, image, general_image, general_file, general_media)
    * @param size               string - for allowed file size (1.5mb)
    * @param limit              int - for allowed upload limit (2)
    * @param dimension_size     array - for image dimension size.
    * 
    */
    public function run(string $fileUploadName, string $folder_create = "default", string $upload_dir, string $type = NULL, string $size = '2mb', int $limit_max = 1, array $dimension_size = [])
    {
        if (isset($_FILES[$fileUploadName])) 
        {
            //Create base folder
            $this->baseFolderCreate($upload_dir);
            
            //format size to bytes
            $this->internal['size'] = $this->sizeToBytes($size);

            //size limit
            $this->internal['size_limit'] = $this->sizeLimit($this->internal['size']); 

            //First we rearrange our upload file data
            $this->internal['rearrange'] = $this->reArrangePostFiles($_FILES[$fileUploadName]);

            //storage
            $storage = [];

            /**
            * if input name is not an array - error 500
            */
            if($this->internal['rearrange'] === $this->error['500'])
            {
                $this->data['status']   = $this->error['500'];
                $this->data['message']  = sprintf("Input file `name[]` (%s) must be passed as an array.", $fileUploadName);
            }
            
            //check if upload data is an array
            if(is_array($this->internal['rearrange'])){

                //count total files
                $this->internal['count'] = count($this->internal['rearrange']);

                //Start loop process
                foreach($this->internal['rearrange'] as $key => $file)
                {
                    //Collect img datas 
                    $file_name      =   $this->internal['rearrange'][$key]['name'];
                    $file_type      =   $this->internal['rearrange'][$key]['type'];
                    $file_tmp_name  =   $this->internal['rearrange'][$key]['tmp_name'];
                    $file_error     =   $this->internal['rearrange'][$key]['error'];
                    $file_size      =   $this->internal['rearrange'][$key]['size'];
                    $this->success  =   false;

                    /**
                    * check image size error
                    */
                    $attrError = $this->checkImageAttributeError($dimension_size, $fileUploadName);

                    /**
                    * Get file extension
                    */
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

                    /**
                    * Please select a file to upload - error 401
                    */
                    if (empty($file_name)) { 
                        if(in_array(isset($this->error['401']), array_keys($this->error))){
                            $this->data['status']   = $this->error['401'];
                            $this->data['message']  = sprintf("Please select a file to upload", $fileUploadName);
                        }
                        break;
                    }

                    /**
                    * if upload is an image & image size allowed error. - error 405
                    */
                    elseif(!$attrError['response'] && in_array($type, ['images', 'general_image']))
                    {
                        if(in_array(isset($this->error['405']), array_keys($this->error))){
                            $this->data['status']   = $this->error['405'];
                            $this->data['message']  = sprintf($attrError['message'], $fileUploadName);
                        }
                        break;
                    }
                    
                    /**
                    * File upload size is bigger than allowed size limit. - error 402
                    */
                    elseif($file_size > $this->internal['size'])
                    {
                        $this->removeUploadedFile($this->internal['folder_upload']);
                        if(in_array(isset($this->error['402']), array_keys($this->error))){
                            $this->data = [
                                "status"    => $this->error['402'], 
                                "message"   => sprintf("%s <br> File upload size is bigger than allowed size limit of %smb <br>", $fileUploadName, $this->internal['size_limit']), 
                                "file"      => $file_name, 
                                "ext"       => $file_extension
                            ];
                        }
                        break;
                    }

                    /**
                    * Maximum upload allowed exceeded. - error 403
                    */
                    elseif($this->internal['count'] > $limit_max)
                    {
                        $text = $this->fileText($limit_max);
                        if(in_array(isset($this->error['403']), array_keys($this->error))){
                            $this->data = [
                                "status"    => $this->error['403'], 
                                "message"   => sprintf("%s <br> Maximum upload allowed exceeded, you can upload upto %s%s only.<br>", $fileUploadName, $limit_max, $text), 
                                "file"      => $file_name, 
                                "ext"       => $file_extension
                            ];
                        }
                        break;
                    }

                    /**
                    * Uploded file format not allowed. - error 404
                    */
                    elseif(!in_array($file_type, $this->allowedType()['mime'][$type]))
                    {
                        $splint_img_ext = implode(' ', $this->allowedType()['extension'][$type]);
                        if(isset($this->internal['data'])){ $this->removeUploadedFile($this->internal['folder_upload']); }
                        if(in_array(isset($this->error['404']), array_keys($this->error))){
                            $this->data = [
                                "status"    => $this->error['404'], 
                                "message"   => sprintf("%s <br> Uploaded file format not allowed; allowed formats are %s <br>", $fileUploadName, $splint_img_ext), 
                                "file"      => $file_name,
                                "ext"       => $file_extension
                            ];
                        }
                        break;
                    }

                    /**
                    * No error response (Uploading successfully started). - error 200
                    */
                    elseif($this->send = true)
                    {
                        $this->success = true;

                        //create children folder structure
                        $this->createChildrenFolder($folder_create, $upload_dir);

                        //new generated file name
                        $new_gen_file = $this->generateNewFileData($file_extension);

                        //get storage path
                        $storage = $this->getFolderStorage($folder_create, $upload_dir, $new_gen_file['name']);
                        
                        if(in_array($file_type, $this->allowedType()['mime'][$type])){    
                            if( move_uploaded_file($file_tmp_name, $storage['filePath']) ){
                                array_push($this->internal['data'], $new_gen_file['name']); //new uploaded - generated image
                                array_push($this->internal['raw_upload'], $file_name); //raw image file name
                                
                                array_push($this->internal['folder_upload'], $storage['folderPath']); // folder upload path
                                array_push($this->internal['folder_real_path'], dirname($storage['filePath']) . "/{$new_gen_file['name']}"); // folder real upload path
                                array_push($this->internal['folder_upload_url'], $this->base_url . "{$storage['folderPath']}"); // folder url upload path
                                array_push($this->internal['ext_files'], $new_gen_file['extension']); // extension for uploaded files
                            }
                        }
                    } 
                }
            }

            //Return on successful upload
            if($this->send && $this->success){
                //add to instance of this for internal space usage
                $this->folder = $this->internal['folder_real_path'];
                $this->image = $this->internal['data'];
                $this->settings = dirname($storage['filePath']); //compressed dir path

                $this->data = [
                    "status"    => $this->error['200'], 
                    "message"   => sprintf("%s <br> Uploaded successfully <br>", $fileUploadName), 
                    "file"      => [
                        "image"             => $this->internal['raw_upload'],  
                        "new_image"         => $this->internal['data'],
                        "file"             => $this->internal['raw_upload'],  
                        "new_file"         => $this->internal['data'],
                        "folder"            => $this->internal['folder_upload'],
                        "folder_real_path"  => $this->internal['folder_real_path'], 
                        "folder_url"        => $this->internal['folder_upload_url']
                    ],
                    "ext" => $this->internal['ext_files']
                ];
            }
            
        }
        
        /**
        * No File Upload found. ERROR_400
        */
        else
        {
            if(isset($this->error['400'])){
                $this->data['status']   = $this->error['400'];
                $this->data['message']  = "No File Upload found";
            }
        }

        return $this;
    }


    /**
    * @param  callable     function - for error message handling.
    * @return response     class object data on error.
    */
    public function error(callable $function)
    {
        if(!$this->success){
            if(is_callable($function)){
                if($this->data['status'] !== 0){
                    $function($this);
                }
            }
        }
        return $this;
    }


    /**
    * @param  callable     function - for success on upload handling.
    * @return response     class object data on success.
    */
    public function success(callable $function)
    {
        if($this->send && $this->success){
            if(is_callable($function)){
                $function($this);
            }
        }
        return $this;
    }
    
    /**
    * Image compressor
    * @param boolean $compress
    * @return void|null
    */
    public function compress($compress = false)
    {
        if($this->success && $compress){

            $ImgCompressor = new ImgCompressor($this->settings);
            if(!is_null($this->folder)){
                //Loop through folder images
                foreach($this->folder as $key => $folder_val){
                    //Loop through image
                    foreach($this->image as $i_key => $i_val){
                        $ImgCompressor->run($folder_val, pathinfo($i_val, PATHINFO_EXTENSION), 5);
                    }
    
                }
            }
        }
    }

    /**
    * Return first uploaded data
    * @return array|null
    */
    public function first()
    {
        // get upload data
        $data = $this->data['file'];

        // return only first data
        if(isset($data['image'][0])){
            return [
                'image' => $data['image'][0],
                'new_image' => $data['new_image'][0],
                'file' => $data['file'][0],
                'new_file' => $data['new_file'][0],
                'folder' => $data['folder'][0],
                'folder_real_path' => $data['folder_real_path'][0],
                'folder_url' => $data['folder_url'][0] 
            ];
        }
    }

    /**
    * Return all uploaded data
    * @return array|null
    */
    public function get()
    {
        return $this->data['file'];
    }

    /**
    * @param  int|float  $response interger or float passer.
    * @param  string|array|object     $message can be any data type for display.
    * @return json       Returns encoded JSON object of response and message
    */
    public function echoJson(?int $response = 0, $message = null)
    {
        echo json_encode(['response' => $response, 'message' => $message]);
    }

    /**
    * @param  string base directory path.
    * @return string|void|null 
    */
    protected function optionDir(?string $dir = null)
    {
        if(empty($dir)){
            // for laravel or other framework that supports public path func
            if (function_exists('public_path')) {
                $dir = $this->clean_path(public_path());
            }else{
                // get default project root document path
                $dir = $this->clean_path(realpath("."));
            }
        }else{
            $dir = $this->clean_path($dir);
        }

        return $dir;
    }

    /**
    * @param  string $path url path.
    * @return string|null
    */
    protected function optionUrl(?string $path = null)
    {
        if(empty($path)){
            $path = $this->getURL();
        }

        return $path;
    } 
    
    /**
    * Image watermarks
    * @param  string $stamp path to watermark image 
    * Example is --- assets/img/wateramrk.png | no need for base dir or full image path
    * @param  float|int @marge_right position of watermark margin right
    * @param  float|int @marge_bottom position of watermark margin bottom
    * @param  boolean @waterMark default is set to false, set to true to execute the watermark.
    * @return void|null 
    */
    public function waterMark($stamp, $marge_right = 50, $marge_bottom = 50, $waterMark = false)
    {
        //file does not exists
        if(!file_exists($this->base_dir . $stamp) || is_dir($this->base_dir . $stamp))
            return;
        
        // Load the watermark stamp 
        @$stamp = @imagecreatefrompng($this->base_dir . $stamp);

        if(!is_null($this->folder) && $waterMark){
            //Loop through folder
            foreach($this->folder as $key => $folder_val)
            {
                //Loop through image
                foreach($this->image as $i_key => $i_val)
                {
                    //Get file extension
                    $file_extension = strtolower(pathinfo($i_val, PATHINFO_EXTENSION));

                    //path to destination image
                    if($file_extension == 'png'){
                        $im = @imagecreatefrompng($folder_val);
                    }else{
                        if($file_extension == 'webp'){
                            $im = @imagecreatefromwebp($folder_val);
                        }else{
                            $im = @imagecreatefromjpeg($folder_val);
                        }
                    }

                    // Set the margins for the stamp and get the height/width of the stamp image
                    $marge_right = $marge_right;
                    $marge_bottom = $marge_bottom;
                    @$sx = imagesx($stamp); //height -x
                    @$sy = imagesy($stamp); //width -y

                    // Copy the stamp image onto our photo using the margin offsets and the photo 
                    // width to calculate positioning of the stamp. 
                    @imagecopy($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));

                    // Output and free memory
                    //header('Content-type: image/png');
                    //imagepng($im);

                    if($file_extension == 'png'){
                        @imagepng($im, $folder_val, 8, PNG_FILTER_AVG);
                    }else{
                        if($file_extension == 'webp'){
                            @imagewebp($im, $folder_val, 100);
                        }else{
                            @imagejpeg($im, $folder_val, 90);
                        }
                    }
                    //Free up memory
                    imagedestroy($im);
                }
            }
        }
        
    }

    /**
    * Generate new file data
    * @param string  $extension image/file extension
    * @return array
    */
    private function generateNewFileData($extension)
    {
        $new_name = $this->timeBaseFolder()['now'] . str_shuffle(substr(md5(rand(1000000000, 999999999)), 0, 15));
        return ['name' => strtolower("{$new_name}.$extension"), 'extension' => $extension];
    }
    
    /**
    * Image resize
    * @param  float|int @crop_width width
    * @param  float|int @crop_height height
    * @param  boolean @autoResize default is set to false, set to true to execute the resize.
    * @return void|null 
    */
    public function imageAutoResize($crop_width = null, $crop_height = null, $autoResize = false)
    {
        if(!is_null($this->folder) && $autoResize){
            //Loop through the image data arrays
            foreach($this->folder as $key => $folder_val)
            {
                //Loop through image
                foreach($this->image as $i_key => $i_val)
                {
                    //Get file extension
                    $file_extension = strtolower(pathinfo($i_val, PATHINFO_EXTENSION));

                    //path to destination image
                    if($file_extension == 'png'){
                        $new = @imagecreatefrompng("$this->settings/$i_val");
                    }else{
                        if($file_extension == 'webp'){
                            $new = @imagecreatefromwebp("$this->settings/$i_val");
                        }else{
                            $new = @imagecreatefromjpeg("$this->settings/$i_val");
                        }
                    }

                    //Get the image size from the image
                    $get_width = imagesy($new); //width -y
                    $get_height = imagesx($new); //height -x

                    //get the min length from both size
                    $size = min($get_width, $get_height);
                    //$size = min($crop_width, $crop_height);

                    //Start cropping
                    if($get_width >= $get_height) {
                        $newy = ($get_width - $get_height)/2;
                        $im2 = imagecrop($new, ['x' => 0, 'y' => $newy, 'width' => $size, 'height' => $size]);
                    }
                    else {
                        $newx = ($get_height - $get_width)/2;
                        $im2 = imagecrop($new, ['x' => $newx, 'y' => 0, 'width' => $size, 'height' => $size]);
                    }

                    //Finish image crop
                    if($file_extension == 'png'){
                        imagepng($im2, "$this->settings/$i_val", 8, PNG_FILTER_AVG);
                    }else{
                        if($file_extension == 'webp'){
                            @imagewebp($im2, "$this->settings/$i_val", 100);
                        }else{
                            @imagejpeg($im2, "$this->settings/$i_val", 90);
                        }
                    }
                    //Free up memory
                    imagedestroy($im2);
                }
            }
        }
    }

    /**
    * Get image width and height
    * @param  string $path_to_file string full path to file
    * @return object|array|void|null 
    */
    public function getImageAttribute(string $path_to_file = null)
    {
        if(empty($filename) == false || !file_exists($path_to_file)){
            return;
        }
        
        $ext = strtolower(pathinfo($path_to_file, PATHINFO_EXTENSION));
        if($ext == 'png'){
            $new = @imagecreatefrompng($path_to_file);
        }else{
            if($ext == 'webp'){
                $new = @imagecreatefromwebp($path_to_file);
            }else{
                $new = @imagecreatefromjpeg($path_to_file);
            }
        }
        
        //if image object response
        if(is_object($new) || is_resource($new)){
            return [
                'height'    => imagesy($new), //height -y
                'width'     => imagesx($new) //width -x
            ];
        }
    }

    /**
    * remove uploaded files
    */
    private function removeUploadedFile($folder_upload)
    {
        if(is_array($folder_upload)){
            foreach($folder_upload as $key => $value)
            {
                if (file_exists($this->base_dir . $value)) {
                    @unlink($this->base_dir . $value);
                }
            }
        }
    }

    /**
    * file text formatting
    */
    private function fileText($count = 0)
    {
        return $count > 1 ? 'files' : 'file';
    }

    /**
    * Image size attribute allowed check
    */
    private function checkImageAttributeError($image_error, $fileUploadName)
    {
        $count = count($image_error);
        if($count > 0){
            //check for default same error handler
            if(!isset($image_error['same']))
                $image_error['same'] = false;


            //get temp image upload attribute
            $imageSize = $this->getAttribute($fileUploadName);

            //if an image is found
            if(is_null($imageSize))
                return ['response' => false, 'message' => "Image size could'nt be found. Please check if uploaded image is valid"];
            
            //check for size error handler type
            if($image_error['same'])
            {
                //for one size error check
                if($count === 2){
                    if(isset($image_error['width']) && isset($imageSize['width'])){
                        if($image_error['width'] != $imageSize['width']){
                            return [
                                'response'  => false,
                                'message'   => sprintf("Image dimension allowed is width/%spx", $image_error['width'])
                            ];
                        }
                    }
                    if(isset($image_error['height']) && isset($imageSize['height'])){
                        if($image_error['height'] != $imageSize['height']){
                            return [
                                'response'  => false,
                                'message'   => sprintf("Image dimension allowed is height/%spx", $image_error['height'])
                            ];
                        }
                    }
                }
                //for both size error check
                else{
                    if($image_error['width'] != $imageSize['width']
                        || $image_error['height'] != $imageSize['height']){
                            return [
                                'response'  => false,
                                'message'   => sprintf("Image dimensions allowed is width/%spx by height/%spx", $image_error['width'], $image_error['height'])
                            ];
                    }
                }
            }   

            //check if size is greather than or equal to
            else
            {
                //for one size error check
                if($count === 2){
                    if(isset($image_error['width']) && isset($imageSize['width'])){
                        if($image_error['width'] > $imageSize['width']){
                            return [
                                'response'  => false,
                                'message'   => sprintf("Image dimension allowed must be greater or equal to width/%spx", $image_error['width'])
                            ];
                        }
                    }
                    if(isset($image_error['height']) && isset($imageSize['height'])){
                        if($image_error['height'] > $imageSize['height']){
                            return [
                                'response'  => false,
                                'message'   => sprintf("Image dimension allowed must be greater or equal to height/%spx", $image_error['height'])
                            ];
                        }
                    }
                }
                //for both size error check
                else{
                    if($image_error['width'] > $imageSize['width']
                        || $image_error['height'] > $imageSize['height']){
                            return [
                                'response'  => false,
                                'message'   => sprintf("Image dimensions allowed must be greater or equal to width/%spx by height/%spx", $image_error['width'], $image_error['height'])
                            ];
                    }
                }
            }
            
        }

        return ['response' => true, 'message' => ''];
    }
    
    /**
    * Get image width and height
    * 
    */
    private function getAttribute(string $filename)
    {
        if(empty($filename) || file_exists($_FILES[$filename]['tmp_name'][0]) == FALSE){
            return;
        }
        
        $img = $_FILES[$filename]['tmp_name'][0];
        $ext = strtolower(pathinfo($_FILES[$filename]['name'][0], PATHINFO_EXTENSION));
        if($ext == 'png'){
            $new = @imagecreatefrompng($img);
        }else{
            if($ext == 'webp'){
                $new = @imagecreatefromwebp($img);
            }else{
                $new = @imagecreatefromjpeg($img);
            }
        }

        //if image object response
        if(is_object($new) || is_resource($new)){
            return [
                'height'    => imagesy($new), //height -y
                'width'     => imagesx($new) //width -x
            ];
        }
        return;
    }

    /**
    * filter error to remove unwanted error response
    */
    private function filterError($errorDisallowed)
    {
        if(is_array($errorDisallowed) && count($errorDisallowed) > 0){

            foreach($errorDisallowed as $value){
                if(in_array($value, array_keys($this->error) )){
                    if($value != '500')
                        unset($this->error[$value]);
                }
            }
        }
    }

    /**
     * Get url real path
    * 
    */
    private function getURL($atRoot=FALSE, $atCore=FALSE, $parse=FALSE)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $http       = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $hostname   = $_SERVER['HTTP_HOST'];
            $dir        =  str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

            $core       = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), PREG_SPLIT_NO_EMPTY);
            $core       = $core[0];

            $tmplt      = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
            $end        = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
            $base_url   = sprintf( $tmplt, $http, $hostname, $end );
        }
        else $base_url  = 'http://localhost/';

        if ($parse) {
            $base_url   = parse_url($base_url);
            if (isset($base_url['path'])) if ($base_url['path'] == '/') $base_url['path'] = '';
        }
        return $base_url;
    }

    /**
     * Get url real path
    * @param string @path 
    * @return string path slash '/' replacement
    */
    private function clean_path(?string $path = null)
    {
        return str_replace('\\', '/', $path) . '/';
    }
    
    /**
    * get each image upload folder storage data
    */
    private function getFolderStorage($folder_create, $upload_dir, $new_gen_file)
    {
        switch ($folder_create) 
        {
            case 'year':
                $filePath   = $this->timeBaseFolder($upload_dir)['year'] . '/' . $new_gen_file;
                $folderPath = str_replace($this->base_dir, '', $filePath);
                break;
            case 'month':
                $filePath   = $this->timeBaseFolder($upload_dir)['month'] . '/' . $new_gen_file;
                $folderPath = str_replace($this->base_dir, '', $filePath);
                break;
            case 'day':
                $filePath   = $this->timeBaseFolder($upload_dir)['day'] . '/' . $new_gen_file;
                $folderPath = str_replace($this->base_dir, '', $filePath);
                break;
            default:
                $filePath   = $this->base_dir . $upload_dir .'/'. $new_gen_file;
                $folderPath = str_replace($this->base_dir, '', $filePath);
                break;
        }

        return ['filePath' => $filePath, 'folderPath' => $folderPath];
    }

    /**
    * Create Non Existable Base Folder
    */
    private function baseFolderCreate($upload_dir)
    {
        //Create folder if not exist
        if(!file_exists($this->base_dir . $upload_dir))
        {
            @mkdir($this->base_dir . $upload_dir, 0777);

            //Create index file
            $this->createDefaultRestrict($this->base_dir . $upload_dir);
        }
    }
    
    /**
    * creating new child folder storage structure
    */
    private function createChildrenFolder($folder_create, $upload_dir)
    {
        //Creating our folder structure
        $folder = $this->timeBaseFolder($upload_dir);
        switch ($folder_create) 
        { 
            case 'year':
                if (!file_exists($this->timeBaseFolder($upload_dir)['year'])) {
                    mkdir($folder['year'], 0777);
                    $this->createDefaultRestrict($folder['year']);
                }
                break;
            case 'month':
                if (!file_exists($this->timeBaseFolder($upload_dir)['year'])) {
                    mkdir($folder['year'], 0777);
                    $this->createDefaultRestrict($folder['year']);
                }
                if (!file_exists($this->timeBaseFolder($upload_dir)['month'])) {
                    mkdir($folder['month'], 0777);
                    $this->createDefaultRestrict($folder['month']);
                }
                break;
            case 'day':
                if (!file_exists($this->timeBaseFolder($upload_dir)['year'])) {
                    mkdir($folder['year'], 0777);
                    $this->createDefaultRestrict($folder['year']);
                }
                if (!file_exists($this->timeBaseFolder($upload_dir)['month'])) {
                    mkdir($folder['month'], 0777);
                    $this->createDefaultRestrict($folder['month']);
                }
                if (!file_exists($this->timeBaseFolder($upload_dir)['day'])) {
                    mkdir($folder['day'], 0777);
                    $this->createDefaultRestrict($folder['day']);
                }
                break;
        }
    }
    
    /**
    * creating folder time base structure
    */
    private function timeBaseFolder($upload_dir = null)
    {
        $now = strtotime("now");
        $time = [
            "year"  => date("Y", $now),
            "month" => date("n", $now),
            "day"   => date("j", $now),
            "now"   => $now
        ];
        return [
            'year'  => $this->base_dir . $upload_dir . '/' . $time['year'],
            'month' => $this->base_dir . $upload_dir . '/' . $time['year'] . '/' . $time['month'],
            'day'   => $this->base_dir . $upload_dir . '/' . $time['year'] . '/' . $time['month'] . '/' . $time['day'], 
            'now'   => $time['now']
        ];
    }
    
    /**
    * create default folder restricted files
    */
    private function createDefaultRestrict($path)
    {
        //Create index file
        if (!file_exists("{$path}/index.html") ) {
            @$fsource = fopen("{$path}/index.html", 'w+');
            if(is_resource($fsource)){
                fwrite($fsource, "Restricted Access");
                fclose($fsource);
            }
        }

        //Create apache file -- .htaccess
        if (!file_exists("{$path}/.htaccess") ) {
            @$fsource = fopen("{$path}/.htaccess", 'w+');
            if(is_resource($fsource)){
                fwrite($fsource, "");
                fclose($fsource);
            }
        }
    }
    
    /**
    * allowed MimeType and Extension Types
    */
    private function allowedType()
    {
        //Extension MimeType
        $this->mimeType = [
            'video'         =>  ['video/mp4','video/mpeg','video/quicktime','video/x-msvideo','video/x-ms-wmv'],
            'audio'         =>  ['audio/mpeg','audio/x-wav'],
            'files'         =>  ['application/msword','application/pdf','text/plain'],
            'images'        =>  ['image/jpeg', 'image/png'],
            'general_image' =>  ['image/jpeg', 'image/png', 'image/webp'],
            'general_file'  =>  [
                'application/msword','application/pdf','text/plain','application/zip', 'application/x-zip-compressed', 'multipart/x-zip',
                'application/x-zip-compressed', 'application/x-rar-compressed', 'application/octet-stream', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ],
            'general_media' =>  ['audio/mpeg','audio/x-wav', 'video/mp4','video/mpeg','video/quicktime','video/x-msvideo','video/x-ms-wmv']
        ];        

        //Extension Type
        $this->extensionType = [
            'video'         =>  ['.mp4', '.mpeg', '.mov', '.avi', '.wmv'],
            'audio'         =>  ['.mp3', '.wav'],
            'files'         =>  ['.docx', '.pdf', '.txt'],
            'images'        =>  ['.jpg', '.jpeg', '.png'],
            'general_file'  =>  ['.docx', '.pdf', '.txt', '.zip', '.rar', '.xlsx', '.xls'],
            'general_image' =>  ['.jpg', '.jpeg', '.png', '.webp'],
            'general_media' =>  ['.mp3', '.wav', '.mp4', '.mpeg', '.mov', '.avi', '.wmv']
        ];
        
        return ['mime' => $this->mimeType, 'extension' => $this->extensionType];
    }
    
    /**
    * Form size to bytes | formatted values * 1024
    * @return int of size formated to bytes
    */
    private function sizeToBytes($size)
    {
        $size       = str_replace(',', '.', $size);
        $replace    = trim(str_replace('mb', '', strtolower($size)));
        $point      = strpos($replace, '.');

        if($point)
            $replace    = $replace . '00';
        else $replace   = $replace . '000';

        $replace = str_replace('.', '', $replace);
        return $replace * 1024;
    }
    
    /**
    * Form size to bytes | formatted values * 1024
    * @return int of size formated to bytes
    */
    private function sizeLimit($size)
    {
        return round(($size / 1024) / 1024, 2);
    }
    
    /**
    * Rearrange arrays files
    */
    private function rearrange($array)
    {
        foreach( $array as $key => $all ){
            foreach( $all as $i => $val ){
                $new[$i][$key] = $val;   
            }   
        }
        return @$new;
    }
    
    /**
    * Re-arrange data array files
    */
    private function reArrangePostFiles(&$file_post)
    {
        if(!is_array($file_post['name'])){
            return $this->error['500'];
        }
        
        $file_ary   = array();
        $file_count = count($file_post['name']);
        $file_keys  = array_keys($file_post);

        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }
        return $file_ary;
    }
}



/***********************************************************
* #### PHP Image Compressor Class ####
***********************************************************/

class ImgCompressor {	
	
    public $setting;

	public function __construct($setting) 
	{
		$this->setting = array(
			'directory' => $setting, // directory file compressed output
			'file_type' => array( // file format allowed
				'image/jpeg',
				'image/png',
				'image/gif',
                'image/webp'
			) 
		);
	}
	
	private function create($image, $name, $type, $size, $c_type, $level) 
	{
		$im_name = $name;
		$im_output = $this->setting['directory'].'/'.$im_name;
		$im_ex = explode('.', $im_output); // get file extension
		
		// create image
		if($type == 'image/jpeg'){
			$im = @imagecreatefromjpeg($image); // create image from jpeg
		}else if($type == 'image/gif'){
			$im = @imagecreatefromgif($image); // create image from gif
		}else if($type == 'image/webp'){
			$im = @imagecreatefromwebp($image); // create image from webp
		}else{
			$im = @imagecreatefrompng($image);  // create image from png (default)
		}
		
		// compree image
		if(in_array($c_type, array('jpeg','jpg','JPG','JPEG'))){
			if(!empty($level)){
				@imagejpeg($im, $im_output, 100 - ($level * 10)); // if level = 2 then quality = 80%
			}else{
				@imagejpeg($im, $im_output, 100); // default quality = 100% (no compression)
			}
			$im_type = 'image/jpeg';
		}else if(in_array($c_type, array('gif','GIF'))){
			if($this->check_transparent($im)) { // Check if image is transparent
				imageAlphaBlending($im, true);
				imageSaveAlpha($im, true);
				@imagegif($im, $im_output);
			}
			else {
				@imagegif($im, $im_output);
			}
			$im_type = 'image/gif';
		}else if(in_array($c_type, array('webp','WEBP'))){
			if($this->check_transparent($im)) { // Check if image is transparent
				imageAlphaBlending($im, true);
				imageSaveAlpha($im, true);
				@imagewebp($im, $im_output);
			}
			else {
				@imagewebp($im, $im_output);
			}
			$im_type = 'image/webp';
		}else if(in_array($c_type, array('png','PNG'))){
			if($this->check_transparent($im)) { // Check if image is transparent
				imageAlphaBlending($im, true);
				imageSaveAlpha($im, true);
				@imagepng($im, $im_output, $level); // if level = 2 like quality = 80%
			}
			else {
				@imagepng($im, $im_output, $level); // default level = 0 (no compression)
			}
			$im_type = 'image/png';
		}
		
		// image destroy
		imagedestroy($im);
		
		// output original image & compressed image
		$im_size = filesize($im_output);
		$data = array(
			'original'  => array(
				'name'  => $name,
				'image' => $image,
				'type'  => $type,
				'size'  => $size
			),
			'compressed'    => array(
				'name'      => $im_name,
				'image'     => $im_output,
				'type'      => $im_type,
				'size'      => $im_size
			)
		);
		return $data;
	}

	private function check_transparent($im) 
	{

		$width = imagesx($im); // Get the width of the image
		$height = imagesy($im); // Get the height of the image

		// We run the image pixel by pixel and as soon as we find a transparent pixel we stop and return true.
		for($i = 0; $i < $width; $i++) {
			for($j = 0; $j < $height; $j++) {
				$rgba = imagecolorat($im, $i, $j);
				if(($rgba & 0x7F000000) >> 24) {
					return true;
				}
			}
		}

		// If we dont find any pixel the function will return false.
		return false;
	}  
	
	public function run($image, $c_type, $level = 0) 
	{
		
		// get file info
		$im_info    = @getImageSize($image);
		$im_name    = basename($image);
		$im_type    = $im_info['mime'] ?? '';
		$im_size    = @filesize($image);
        $extension  = pathinfo(strtolower($image), PATHINFO_EXTENSION);
                
		// result
		$result = array();
		
		// cek & ricek
		if(in_array($c_type, array('jpeg','jpg','JPG','JPEG','gif','GIF','png','PNG', 'webp', 'WEBP'))) { // jpeg, png, gif only
			if(in_array($im_type, $this->setting['file_type'])){
				if($level >= 0 && $level <= 9){
					$result['status'] = 'success';
                    if($extension == 'png'){
                        $level = 9;
                    }
					$result['data'] = $this->create($image, $im_name, $im_type, $im_size, $c_type, $level);
				}else{
					$result['status'] = 'error';
					$result['message'] = 'Compression level: from 0 (no compression) to 9';
				}
			}else{
				$result['status'] = 'error';
				$result['message'] = 'Failed file type';
			}
		}else{
			$result['status'] = 'error';
			$result['message'] = 'Failed file type';
		}
		
		return $result;
	}
	
}

