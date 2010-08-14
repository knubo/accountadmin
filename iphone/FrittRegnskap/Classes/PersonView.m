//
//  PersonView.m
//
//  Created by Knut Erik Borgen on 13.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "PersonView.h"
#import "model/Person.h"

@implementation PersonView

@synthesize people;
@synthesize indexes;
@synthesize indexNames;

- (void)dealloc {

	[people release];
	[indexes release];
	[indexNames release];
	
    [super dealloc];
}

- (void)loadPeople {
	if(people != nil) {
		[people release];
	}
	if(indexes != nil) {
		[indexes release];
		[indexNames release];
	}
	people = [[appDelegate getPeople:true] retain];
	[self calculateIndexes];
	
}

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

		//NSLog(@"Name:%@", name);
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
	return nil;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
	
	
    static NSString *MyIdentifier = @"MyIdentifier";
	
    // Try to retrieve from the table view a now-unused cell with the given identifier.
	
    UITableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:MyIdentifier];
	
    
	
    // If no cell is available, create a new one using the given identifier.
	
    if (cell == nil) {
		
        // Use the default cell style.
		
        cell = [[[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:MyIdentifier] autorelease];
		
    }
	
    int section = indexPath.section;
	
	NSNumber *pos = (NSNumber *)[indexes objectAtIndex:section];
	
	
	int indexRow = indexPath.row;
	int row = indexRow	+ [pos intValue];
	
    // Set up the cell.
	Person *person = [people objectAtIndex: row];
	
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


@end
