# SmartShelf API for NeWave Sensor Solutions

This is the very basic API requred to collect data from the Nobel Readers used by NeWave Sensor Solutions and their affiliates. 

## Data

The data comes in two formats to two endpoints

## POG Data

Planogram data is the file that configures the pushers and the products associated to them. They store information related to the position of the pusher among the shelves (vertical positioning) and the position along the shelf (horizontal positioning). The planogram is responsible for linking the tray_tag to the upc. The tray_tag is the primary identifier for the pusher. This tray_tag is also used in the inventory data. Lastly, the POG data also identifies the total number of tags in a particular pusher in the case that some shelves are deeper than others.

## Inventory Data

Inventory data can also come in one of two forms. The data type is set in the header of the file along with the mac_address of the Nobel Reader sending the data. Additionally, there is a timestamp in the header of the data as well. The data type can be set to either "item" or "tag" and this signifies how the data is transmitted to the cloud. In cases where the data_type is set to "tag", the data recevied by the cloud needs to be converted to "item_count" data by the getItemCount algorithm provided by NeWaveSensorSolutions. This takes the following arguments (product_depth_in_inches, number_of_tags_blocked, and if the product has foil packaging whether_the_paddle_was_exposed). The algorithm then provides the item_count. If the "data_type" is set to "item", then the tag data was already converted to item_count data on the Nobel Reader. As such, no conversion of this data is required.

## KPI Calculation

As outlined by our client, there are several Key Performance Indicators that they wish to have calculated at a "store level" and because the data coming in to the cloud is a snapshot of the store inventory, these KPI are calculated when the data is received at the cloud as opposed to when the data is pulled from the DB for reporting. 

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

This software is proprietary software.
