
cp -rf /data/html/Prod/KBCommons_multi/resources/views/system/tools/PhenoDistTool /home/chanye/projects/

mkdir -p /home/chanye/projects/PhenoDistTool/controller
mkdir -p /home/chanye/projects/PhenoDistTool/routes

cp -rf /data/html/Prod/KBCommons_multi/app/Http/Controllers/System/Tools/KBCToolsPhenoDistToolController.php /home/chanye/projects/PhenoDistTool/controller/

cp -rf /data/html/Prod/KBCommons_multi/public/system/home/PhenoDistTool/* /home/chanye/projects/PhenoDistTool/

grep -e "Phenotype Distribution Tool" -e "PhenoDistTool" /data/html/Prod/KBCommons_multi/routes/web.php | grep -v -e "PhenoDistTool2" -e "^//" > /home/chanye/projects/PhenoDistTool/routes/web.php
