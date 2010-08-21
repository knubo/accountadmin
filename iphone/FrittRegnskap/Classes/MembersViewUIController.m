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
@synthesize lookupMembership;

- (void)dealloc {
	
	[people release];
	[indexes release];
	[indexNames release];
	[lookupMembership release];
	
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

- (void) setSemesterTitle {
  NSArray *semesters = [appDelegate getOneObjectFromDatabase:@"Semester" idfield:@"semester" idvalue:current_semester];
	
	if([semesters count] == 0) {
		return;
	}
	
	Semester *semester = [semesters objectAtIndex:0];
	
	titleBar.title = [semester desc];

}

- (void) fillIndexWitSemesterhMembership {
	[lookupMembership removeAllObjects];
	
	NSArray *memberships = [appDelegate getObjectsFromDatabase:false entity:@"SemesterMembership"];
	
	SemesterMembership *member;
	for (member in memberships) {
		
	}
	
}

- (void) switchToSemesterView {
	semesterView = true;
	people = [appDelegate getObjectsFromDatabase:true entity:@"Person"];	
	
	[self setSemesterTitle];
	[self fillIndexWitSemesterhMembership];
	
	
	
}
@end
