//
//  Semester.h
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 11.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import <CoreData/CoreData.h>


@interface Semester :  NSManagedObject  
{
}

@property (nonatomic, retain) NSNumber * semester;
@property (nonatomic, retain) NSString * description;
@property (nonatomic, retain) NSNumber * year;
@property (nonatomic, retain) NSNumber * fall;

@end



