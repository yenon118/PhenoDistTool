
cp -rf /data/sites/KBCommons/resources/views/system/tools/PhenoDistTool /home/chanye/projects/

mkdir -p /home/chanye/projects/PhenoDistTool/controller
mkdir -p /home/chanye/projects/PhenoDistTool/routes

cp -rf /data/sites/KBCommons/app/Http/Controllers/System/Tools/KBCToolsPhenoDistToolController.php /home/chanye/projects/PhenoDistTool/controller/

cp -rf /data/sites/KBCommons/public/system/home/PhenoDistTool/* /home/chanye/projects/PhenoDistTool/

grep -e "Phenotype Distribution Tool" -e "PhenoDistTool" /data/sites/KBCommons/routes/web.php | grep -v -e "PhenoDistTool2" -e "^//" > /home/chanye/projects/PhenoDistTool/routes/web.php
