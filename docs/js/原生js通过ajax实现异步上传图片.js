
原生js通过ajax实现异步上传图片

var uploadQue = [];
upFiles = document.getElementById('file-upload').files;
var uploadQueLen = upFiles.length;
uploadQue = upFiles;
var uploadIndex = 0;
var uploadCls = {
		
		uploadStart: function() {
			var self = this;
			if(uploadIndex < uploadQueLen){
				var fd = new FormData();
				fd.append("iOrderID", 123);
				fd.append("iCategoryType", iCategoryType);
				fd.append("iFileType", iFileType);
				fd.append("fileToUpload", uploadQue[uploadIndex]);
				var oXHR = new XMLHttpRequest();
				oXHR.upload.addEventListener('progress', self.uploadProgress, false);
				oXHR.addEventListener('load', self.uploadSuccess, false);

				oXHR.open('POST', '/efq/mobile/fileUpload/upload');
				oXHR.send(fd);
				uploadIndex++;
			}
		}

		uploadProgress: function() {
		
		
		}

}