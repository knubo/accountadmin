//
//  CourseMembership.h
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 11.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import <CoreData/CoreData.h>

@class Person;

@interface SemesterMembership :  NSManagedObject  
{
}

@property (nonatomic, retain) NSNumber * memberid;
@property (nonatomic, retain) NSNumber * semester;
@property (nonatomic, retain) NSString * type;

@end



