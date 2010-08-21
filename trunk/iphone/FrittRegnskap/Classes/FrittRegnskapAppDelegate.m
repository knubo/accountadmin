//
//  FrittRegnskapAppDelegate.m
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright Knubo Borgen 2010. All rights reserved.
//

#import "FrittRegnskapAppDelegate.h"
#import "FrittRegnskapViewController.h"
#import "model/Semester.h"
#import "model/CourseMembership.h"
#import "model/YearMembership.h"

@implementation FrittRegnskapAppDelegate

@synthesize window;
@synthesize viewController;

@synthesize managedObjectContext;
@synthesize managedObjectModel;
@synthesize persistentStoreCoordinator;


#pragma mark -
#pragma mark Application lifecycle

- (BOOL)application:(UIApplication *)application didFinishLaunchingWithOptions:(NSDictionary *)launchOptions {    
    
    // Override point for customization after application launch.

    // Add the view controller's view to the window and display.
    [window addSubview:viewController.view];
    [window makeKeyAndVisible];

    return YES;
}


- (void)applicationWillResignActive:(UIApplication *)application {
    /*
     Sent when the application is about to move from active to inactive state. This can occur for certain types of temporary interruptions (such as an incoming phone call or SMS message) or when the user quits the application and it begins the transition to the background state.
     Use this method to pause ongoing tasks, disable timers, and throttle down OpenGL ES frame rates. Games should use this method to pause the game.
     */
}


- (void)applicationDidEnterBackground:(UIApplication *)application {
    /*
     Use this method to release shared resources, save user data, invalidate timers, and store enough application state information to restore your application to its current state in case it is terminated later. 
     If your application supports background execution, called instead of applicationWillTerminate: when the user quits.
     */
}


- (void)applicationWillEnterForeground:(UIApplication *)application {
    /*
     Called as part of  transition from the background to the inactive state: here you can undo many of the changes made on entering the background.
     */
}


- (void)applicationDidBecomeActive:(UIApplication *)application {
    /*
     Restart any tasks that were paused (or not yet started) while the application was inactive. If the application was previously in the background, optionally refresh the user interface.
     */
}


- (void)applicationWillTerminate:(UIApplication *)application {
    /*
     Called when the application is about to terminate.
     See also applicationDidEnterBackground:.
     */
}


#pragma mark -
#pragma mark Memory management

- (void)applicationDidReceiveMemoryWarning:(UIApplication *)application {
    /*
     Free up as much memory as possible by purging cached data objects that can be recreated (or reloaded from disk) later.
     */
}


- (void)dealloc {
    [viewController release];
    [window release];
	
	[managedObjectContext release];
	[managedObjectModel release];
	[persistentStoreCoordinator release];
	
    [super dealloc];
}


- (void) saveSemesterMemberships:(NSArray*) memberships type:(NSString*)type {
	
	for (int i = 0; i < [memberships count]; i++) {
		NSDictionary *semestermembership = [memberships objectAtIndex:i];
		
		YearMembership *newSemester = [NSEntityDescription insertNewObjectForEntityForName:@"SemesterMembership" inManagedObjectContext: [self managedObjectContext]];
		
		NSEnumerator *keys = [semestermembership keyEnumerator];
		id key;
		
		
		[newSemester setValue:type forKey:@"type"];
		
		while ((key = [keys nextObject])) {
			[newSemester setValue:[semestermembership valueForKey:key] forKey:key];
		}
	}
	
	NSError *error = nil;
	
	[[self managedObjectContext] save:&error];
	
	if(error != nil) {
		NSLog(@"Error in save %@", error);
	} else {
		[viewController flagDataAsReloaded];
	}
	
}


- (void) saveYearMemberships:(NSArray*) memberships {
	
	for (int i = 0; i < [memberships count]; i++) {
		NSDictionary *yearmembership = [memberships objectAtIndex:i];
		
		YearMembership *newSemester = [NSEntityDescription insertNewObjectForEntityForName:@"YearMembership" inManagedObjectContext: [self managedObjectContext]];
		
		NSEnumerator *keys = [yearmembership keyEnumerator];
		id key;
		
		
		while ((key = [keys nextObject])) {
			[newSemester setValue:[yearmembership valueForKey:key] forKey:key];
		}
	}
	
	NSError *error = nil;
	
	[[self managedObjectContext] save:&error];
	
	if(error != nil) {
		NSLog(@"Error in save %@", error);
	} else {
		[viewController flagDataAsReloaded];
	}
	
}

- (void) saveSemesters:(NSArray *)jsonSemesters {
	
	for (int i = 0; i < [jsonSemesters count]; i++) {
		NSDictionary *semester = [jsonSemesters objectAtIndex:i];
		
		Semester *newSemester = [NSEntityDescription insertNewObjectForEntityForName:@"Semester" inManagedObjectContext: [self managedObjectContext]];
		
		NSEnumerator *keys = [semester keyEnumerator];
		id key;
		
		
		while ((key = [keys nextObject])) {
			if([key isEqualToString:@"description"]) {
				[newSemester setValue:[semester valueForKey:key] forKey:@"desc"];
			} else {
				[newSemester setValue:[semester valueForKey:key] forKey:key];
			}
		}
	}
	
	NSError *error = nil;
	
	[[self managedObjectContext] save:&error];
	
	if(error != nil) {
		NSLog(@"Error in save %@", error);
	} else {
		[viewController flagDataAsReloaded];
	}
	
}

- (void) savePersons:(NSArray*) persons {
	
	for (int i = 0; i < [persons count]; i++) {
		NSDictionary *person = [persons objectAtIndex:i];
		
		Person *newPerson = [NSEntityDescription insertNewObjectForEntityForName:@"Person" inManagedObjectContext: [self managedObjectContext]];
		
		NSEnumerator *keys = [person keyEnumerator];
		id key;
		
		
		while ((key = [keys nextObject])) {
			[newPerson setValue:[person valueForKey:key] forKey:key];
		}
	}
	
	NSError *error = nil;
	
	[[self managedObjectContext] save:&error];
	
	if(error != nil) {
		NSLog(@"Error in save %@", error);
	} else {
		[viewController flagDataAsReloaded];
	}
}

- (void) deleteObjectsInDatabase: (NSString*) entity {
	NSManagedObjectContext * context = [self managedObjectContext];

	NSArray * result = [self getObjectsFromDatabase:false entity:entity];
	for (id person in result) {
		[context deleteObject:person];
	}
}

- (NSArray *) getOneObjectFromDatabase:(NSString*) entity idfield:(NSString*)idfield idvalue:(int)idvalue {
	NSManagedObjectContext * context = [self managedObjectContext];
	NSFetchRequest * fetch = [[NSFetchRequest alloc] init];
	[fetch setEntity:[NSEntityDescription entityForName:entity inManagedObjectContext:context]];

	NSPredicate *predicate = [NSPredicate predicateWithFormat:[NSString stringWithFormat:@"%@=%@", idfield, @"%d"], idvalue];
	[fetch setPredicate:predicate];
	
	NSArray * result = [context executeFetchRequest:fetch error:nil];

	[fetch release];
	return result;
}


- (NSArray *) getObjectsFromDatabase: (bool) sort entity:(NSString*)entity {
	NSManagedObjectContext * context = [self managedObjectContext];
	NSFetchRequest * fetch = [[NSFetchRequest alloc] init];
	[fetch setEntity:[NSEntityDescription entityForName:entity inManagedObjectContext:context]];
	
	NSSortDescriptor *sortFirstName = nil;
	NSSortDescriptor *sortLastName = nil;
 
	if(sort) {
		sortFirstName = [[NSSortDescriptor alloc] initWithKey:@"firstname" ascending:YES selector:@selector(caseInsensitiveCompare:)];
		sortLastName = [[NSSortDescriptor alloc] initWithKey:@"lastname" ascending:YES selector:@selector(caseInsensitiveCompare:)];
		[fetch setSortDescriptors:[NSArray arrayWithObjects:sortFirstName, sortLastName, nil]];
	}
	
	NSArray * result = [context executeFetchRequest:fetch error:nil];
	
	if(sort) {
		[sortFirstName release];
		[sortLastName release];
	}
	[fetch release];
	return result;
}


- (NSManagedObjectContext *) managedObjectContext {
	if (managedObjectContext != nil) {
		return managedObjectContext;
	}
	NSPersistentStoreCoordinator *coordinator = [self persistentStoreCoordinator];
	if (coordinator != nil) {
		managedObjectContext = [[NSManagedObjectContext alloc] init];
		[managedObjectContext setPersistentStoreCoordinator: coordinator];
	}
	
	return managedObjectContext;
}

- (NSManagedObjectModel *)managedObjectModel {
	if (managedObjectModel != nil) {
		return managedObjectModel;
	}
	managedObjectModel = [[NSManagedObjectModel mergedModelFromBundles:nil] retain];
	
	return managedObjectModel;
}

- (NSPersistentStoreCoordinator *)persistentStoreCoordinator {
	if (persistentStoreCoordinator != nil) {
		return persistentStoreCoordinator;
	}
	NSURL *storeUrl = [NSURL fileURLWithPath: [[self applicationDocumentsDirectory]
											   stringByAppendingPathComponent: @"FrittRegnskap.sqlite"]];
	NSError *error = nil;
	persistentStoreCoordinator = [[NSPersistentStoreCoordinator alloc]
								  initWithManagedObjectModel:[self managedObjectModel]];
	if(![persistentStoreCoordinator addPersistentStoreWithType:NSSQLiteStoreType
												 configuration:nil URL:storeUrl options:nil error:&error]) {
		NSLog(@"Error in storeCoordinator %@", error);
	}
	
	return persistentStoreCoordinator;
}

- (NSString *)applicationDocumentsDirectory {
	return [NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES) lastObject];
}



@end
