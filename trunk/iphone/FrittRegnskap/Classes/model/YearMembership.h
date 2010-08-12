//
//  YearMembership.h
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 11.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import <CoreData/CoreData.h>

@class Person;

@interface YearMembership :  NSManagedObject  
{
}

@property (nonatomic, retain) NSNumber * year;
@property (nonatomic, retain) NSNumber * youth;
@property (nonatomic, retain) Person * member;

@end



