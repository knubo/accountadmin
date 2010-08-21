//
//  MembersViewUIController.m
//
//  Created by Knut Erik Borgen on 20.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "MembersViewUIController.h"
#import "FrittRegnskapViewController.h"
#import "model/Person.h"

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

- (void) setYearTitle {
	titleBar.title = [NSString stringWithFormat:@"År %d", current_year];
}

- (void) setSemesterTitle {
	NSArray *semesters = [appDelegate getSomeObjectFromDatabase:@"Semester" idfield:@"semester" idvalue:current_semester];
	
	if([semesters count] == 0) {
		return;
	}
	
	Semester *semester = [semesters objectAtIndex:0];
	
	titleBar.title = [semester desc];

}

- (void) fillIndexWithYearMemberships {
	
	NSArray *memberships = [appDelegate getSomeObjectFromDatabase:@"YearMembership" idfield:@"year" idvalue:current_year];
	
	if(lookupMembership) {
		[lookupMembership release];
	}
	lookupMembership = [NSMutableDictionary dictionaryWithCapacity:[memberships count]];
	[lookupMembership retain];
	
	YearMembership *member;
	for (member in memberships) {
		NSNumber *idKey = [member memberid];
		NSString *key = [NSString stringWithFormat:@"%@", idKey];
		
		[lookupMembership setObject:member forKey:key];
	}
	
	
}

- (void) fillIndexWitSemesterhMembership {
	
	NSArray *memberships = [appDelegate getSomeObjectFromDatabase:@"SemesterMembership" idfield:@"semester" idvalue:current_semester];

	if(lookupMembership) {
		[lookupMembership release];
	}
	lookupMembership = [NSMutableDictionary dictionaryWithCapacity:[memberships count]];
	[lookupMembership retain];
	
	SemesterMembership *member;
	for (member in memberships) {
		NSNumber *idKey = [member memberid];
		NSString *key = [NSString stringWithFormat:@"%@", idKey];

		[lookupMembership setObject:member forKey:key];
	}

}

- (void) filterPeopleBasedOnLookupMemberhsip {

	for (int i = [people count] - 1; i >= 0; i--) {
		Person *person = [people objectAtIndex:i];
		
		NSNumber *idKey = [person ident];
						   
		NSString *key = [NSString stringWithFormat:@"%@", idKey];
		
		if(![lookupMembership objectForKey:key]) {
			[people removeObjectAtIndex:i];
		}
		
	}
	statusBar.title = [NSString stringWithFormat:@"%d medlemmer", [people count]];

}


- (void) switchToYearView {
	semesterView = false;
	people = [[appDelegate getObjectsFromDatabase:true entity:@"Person"] mutableCopy];	
	
	[self setYearTitle];
	[self fillIndexWithYearMemberships];
	[self filterPeopleBasedOnLookupMemberhsip];
	
	[self calculateIndexes];
	[self.tableView reloadData];
	
	[nextButton setEnabled:current_year != max_year];
	[previousButton setEnabled:current_year != min_year];
	
}

- (void) switchToSemesterView {
	semesterView = true;
	people = [[appDelegate getObjectsFromDatabase:true entity:@"Person"] mutableCopy];	
	
	[self setSemesterTitle];
	[self fillIndexWitSemesterhMembership];
	[self filterPeopleBasedOnLookupMemberhsip];

	[self calculateIndexes];
	[self.tableView reloadData];
	
	[nextButton setEnabled:current_semester != max_semester];
	[previousButton setEnabled:current_semester != min_semester];
}

- (void) animateSwitchView {
  [UIView beginAnimations:nil context:NULL];
	[UIView setAnimationDuration:0.8];
	[UIView setAnimationTransition:UIViewAnimationTransitionCurlDown forView:self.view cache:YES];
	if(semesterView) {
		[self switchToSemesterView];
	} else {
		[self switchToYearView];
	}
	[UIView commitAnimations];

}
- (IBAction)toggleYearSemester:(id)sender {
	semesterView = !semesterView;

	[self animateSwitchView];
	
}

- (IBAction)nextClicked:(id)sender{
	
	if(semesterView) {
		current_semester++;
	} else {
		current_year++;
	}
	[self animateSwitchView];	
}


- (IBAction)previousClicked:(id)sender{

	if(semesterView) {
		current_semester--;
	} else {
		current_year--;
	}
	[self animateSwitchView];
}

/* This is duplicated from PersonView.m - fix a bug here, fix a bug there... */

- (void) calculateIndexes {
	indexes = [NSMutableArray arrayWithCapacity:50];
	[indexes retain];
	indexNames = [NSMutableArray arrayWithCapacity:50];
	[indexNames retain];
	
	int count = -1;
	
	NSMutableDictionary *found = [NSMutableDictionary dictionaryWithCapacity:50];
	
	for (id person in people) {
		count++;
		NSString *name = [person firstname];
		
		char startLetter = [[name uppercaseString] characterAtIndex:0];
		
		id key = [NSNumber numberWithChar:startLetter];
		
		if([found objectForKey:key]) {
			continue;
		}
		
		NSNumber *index = [NSNumber numberWithInt:count];
		NSNumber *sindex = [NSNumber numberWithInt:count];
		[found setObject:index forKey:key];
		[indexes addObject:sindex],
		[indexNames addObject: [name substringToIndex:1]];
		
	}
	
	NSLog(@"Indexing done");
	
}

- (NSArray *)sectionIndexTitlesForTableView:(UITableView *)tableView {
	return indexNames;
}

- (NSInteger)tableView:(UITableView *)tableView sectionForSectionIndexTitle:(NSString *)title atIndex:(NSInteger)index {
	return index;
}


- (NSIndexPath *)tableView:(UITableView *)tableView willSelectRowAtIndexPath:(NSIndexPath *)indexPath {
	return indexPath;
}

- (NSString*) calculateCellId:(Person*)person {
	NSNumber *pidKey = [person ident];	
	NSString *pkey = [NSString stringWithFormat:@"%@", pidKey];

	if(!lookupMembership) {
		NSLog(@"No lookupMembership is a problem");
		return @"Oh shit";
	}
	if(semesterView) {	
		SemesterMembership *memberType = [lookupMembership objectForKey:pkey];		
		return [NSString stringWithFormat:@"%@", [memberType type]]; 
	}
	
	YearMembership *yearMembership = [lookupMembership objectForKey:pkey];
	if([[yearMembership youth] intValue] == 1) {
		return @"Y";
	}
	
	return @"A";
	
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
	
    int section = indexPath.section;
	
	NSNumber *pos = (NSNumber *)[indexes objectAtIndex:section];
	
	
	int indexRow = indexPath.row;
	int row = indexRow	+ [pos intValue];
	
    // Set up the cell.
	Person *person = [people objectAtIndex: row];
	
	NSString *MyIdentifier = [self calculateCellId:person];
	
	
    // Try to retrieve from the table view a now-unused cell with the given identifier.
	
    UITableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:MyIdentifier];
	
    
	
    // If no cell is available, create a new one using the given identifier.
	
    if (cell == nil) {
		
        // Use the default cell style.
		
        cell = [[[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:MyIdentifier] autorelease];

		if([MyIdentifier isEqualToString:@"C"]) {
			cell.imageView.image = [UIImage imageNamed: @"K.png"];
		} else if([MyIdentifier isEqualToString:@"T"]) {
			cell.imageView.image = [UIImage imageNamed: @"T.png"];			
		} else if([MyIdentifier isEqualToString:@"Y"]) {
			cell.imageView.image = [UIImage imageNamed: @"U.png"];			
		} 
		
    }
	

	
    NSString *name =  [NSString stringWithFormat: @"%@ %@", [person firstname], [person lastname]];
	
    cell.textLabel.text =name;
	
    
	
    return cell;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
	if(people == nil) {
		NSLog(@"No peoeple to view...");
		return 0;
	}
	
	NSNumber *indexForAllPerson = (NSNumber *)[indexes objectAtIndex:section];
	
	if(section == [indexes count] -1) {
		return [people count] - [indexForAllPerson intValue];
	} else {
		NSNumber *nextIndex = (NSNumber *)[indexes objectAtIndex:(section + 1)];
		
		return [nextIndex intValue] - [indexForAllPerson intValue];
	}
}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
	NSInteger count = [indexes count];
	return count;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
	FrittRegnskapViewController *controller = appDelegate.viewController;
	
	int sectionIndex = [[indexes objectAtIndex:indexPath.section] intValue];
	
	Person *p = [people objectAtIndex: (sectionIndex+indexPath.row)];	
	[controller showPersonDetail:p];
	
}



@end
