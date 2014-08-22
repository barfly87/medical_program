update learningobjective set discipline1=21 where discipline1=30 and auto_id != 2117;
update learningobjective set discipline2=21 where discipline2=30 and auto_id != 2117;
update learningobjective set discipline3=21 where discipline3=30 and auto_id != 2117;
update learningobjective set discipline1=21,discipline2=1,discipline3=1 where auto_id = 2117;

update teachingactivity set type=26 where type=22 and stage=4;
