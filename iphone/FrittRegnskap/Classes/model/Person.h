//
//  Person.h
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 11.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import <CoreData/CoreData.h>


@interface Person :  NSManagedObject  
{
}

@property (nonatomic, retain) NSNumber * ident;
@property (nonatomic, retain) NSString * cellphone;
@property (nonatomic, retain) NSString * phone;
@property (nonatomic, retain) NSString * postnmb;
@property (nonatomic, retain) NSString * lastname;
@property (nonatomic, retain) NSNumber * newsletter;
@property (nonatomic, retain) NSString * lastedit;
@property (nonatomic, retain) NSString * firstname;
@property (nonatomic, retain) NSString * birthdate;
@property (nonatomic, retain) NSString * comment;
@property (nonatomic, retain) NSString * address;
@property (nonatomic, retain) NSNumber * employee;
@property (nonatomic, retain) NSString * city;
@property (nonatomic, retain) NSString * country;
@property (nonatomic, retain) NSString * email;
@property (nonatomic, retain) NSString * gender;
@property (nonatomic, retain) NSNumber * secretaddress;
@end



