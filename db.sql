CREATE TABLE `orderInfo` (
 `id` int NOT NULL AUTO_INCREMENT,
 `shopifyOrderId` varchar(255) NOT NULL,
 `quoteOrderToken` varchar(255) NOT NULL,
 `quoteOrderWarnings` varchar(999) NOT NULL,
 `orderTotal` varchar(255) NOT NULL,
 `orderTax` varchar(255) NOT NULL,
 `orderStatus` varchar(900) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
 `scaleOrderId` varchar(255) NOT NULL,
 `trackingId` varchar(255) NOT NULL,
 `trackingStatus` varchar(999) NOT NULL,
 `date` datetime(6) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;