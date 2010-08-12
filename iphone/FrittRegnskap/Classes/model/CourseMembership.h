//
//  CourseMembership.h
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 11.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import <CoreData/CoreData.h>

@class Person;

@interface CourseMembership :  NSManagedObject  
{
}

@property (nonatomic, retain) Person * member;
@property (nonatomic, retain) NSManagedObject * semester;

@end



