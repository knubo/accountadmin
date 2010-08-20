//
//  MembersViewUIController.m
//
//  Created by Knut Erik Borgen on 20.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "MembersViewUIController.h"

@implementation MembersViewUIController

@synthesize people;
@synthesize indexes;
@synthesize indexNames;

- (void)dealloc {
	
	[people release];
	[indexes release];
	[indexNames release];	
	
	[super dealloc];
}

- (void) initView {
	NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];  
	
	 min_semester = [userDefaults integerForKey:@"frittregnskap.min_semester"];
	 max_semester = [userDefaults integerForKey:@"frittregnskap.max_semester"];
	 min_year = [userDefaults integerForKey:@"frittregnskap.min_year"];
	 max_year = [userDefaults integerForKey:@"frittregnskap.max_year"];
	
	current_year = max_year;
	current_semester = max_semester;
	
	[self switchToSemesterView];
}

- (void) switchToSemesterView {
	people = [appDelegate getObjectsFromDatabase:true entity:@"Person"];	
}
@end
