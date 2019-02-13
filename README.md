# moodle_qformat_qti12
Import questions with the IMS QTI 1.2 format into moodle. Tested with questionpools from ILIAS.

## Supported questiontypes ##

* Single Choice (without pictures yet & only the highest graded option will keep its grade)

### Mapping for Single Choice ###


| ILIAS | Moodle |
| --- | --- |
| Title | Question name |
| Author | Created by |
| Description | - |
| Question | Question text |
| Working Time | - |
| Shuffle Answers | Shuffle the choices? |
| [Highest point value] | Default mark | 
| [qtype=singlechoice] | One or multiple answers [One answer only] |
| -  | Number the choices? [No numbering] |
| Answer Text | Choice |
| Points | Grade[highest QTI-point value as base and then transformed to respective percentages] |
| Correct solution | Combined feedback[For any correct response] |
| At least one answer is not correct | Combined feedback[For any partially correct response] |
| Feedback for every answer | Feedback [per choice] |
| Hints TBD | Hints TBD
| Taxonomies | Tags |